<?php
namespace App\Helpers;
use ReflectionMethod;

class Helper
{
    /**
     * Limpa e formata valores (array ou string) para UTF-8 e caracteres seguros.
     */
    public static function gCleanField($valor)
    {
        if (is_array($valor)) {
            foreach ($valor as $key => $val) {
                $valor[$key] = self::gCleanField($val); // recursão dentro da classe
            }
            return $valor;
        }

        if (!self::check_utf8($valor)) {
            $valor = utf8_encode($valor);
        }
        $valor = str_replace("'", "‘", $valor);
        $valor = str_replace("\"", "“", $valor);
        $valor = trim($valor);

        return $valor;
    }
    
    /**
     * Exibe a mensagem de erro e finaliza a requisição
     */
    public static function emitirErro($mensagem, $statusCode = "400 Bad Request"){
        if (is_array($mensagem)) { $mensagem = implode('; ', $mensagem); }
        $mensagem = ["erro" => $mensagem];
        
        self::finalizarRequisicao($mensagem, $statusCode);
        exit;
    }

    /**
     * Finaliza a requisição com o código de status e a mensagem
    */
    public static function finalizarRequisicao(
        $msg = '',
        $statusCode = null
    ) {
        header("HTTP/1.1 $statusCode");
        self::echoFormatted($msg);
        exit;
    }

    /**
     * Verifica se o JSON é válido, caso não seja, exibe a mensagem de erro
    */
    public static function verificarErroJson($json)
    {
        $erros = array(
            JSON_ERROR_NONE => null,
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        );

        $codigoErro = json_last_error();
        
        if ($codigoErro !== JSON_ERROR_NONE) {
            $jsonError = ["JSON_ERROR_NONE", "JSON_ERROR_DEPTH", "JSON_ERROR_STATE_MISMATCH", "JSON_ERROR_CTRL_CHAR", "JSON_ERROR_SYNTAX", "JSON_ERROR_UTF8"];
            self::emitirErro([
                'message' => sprintf("Erro %s %s", $jsonError[$codigoErro], $erros[$codigoErro]),
            ], "400 Bad Request");
        }

        return ['data' => $json];
    }

    /**
     * Verifica se a string está em UTF-8
     */
    public static function check_utf8($str)
    {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c <= 128) continue;

            if ($c > 247) return false;
            elseif ($c > 239) $bytes = 4;
            elseif ($c > 223) $bytes = 3;
            elseif ($c > 191) $bytes = 2;
            else return false;

            if (($i + $bytes) > $len) return false;

            while ($bytes > 1) {
                $i++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                $bytes--;
            }
        }
        return true;
    }

    /**
     * Formata array/string para UTF-8 recursivamente
     */
    public static function formataJson(&$data)
    {
        $encodings = [
            'UTF-8','UTF-16','UTF-16BE','UTF-16LE','UTF-32','UTF-32BE','UTF-32LE',
            'ISO-8859-1','ISO-8859-2','ISO-8859-3','ISO-8859-4','ISO-8859-5','ISO-8859-6',
            'ISO-8859-7','ISO-8859-8','ISO-8859-9','ISO-8859-10','ISO-8859-11','ISO-8859-13',
            'ISO-8859-14','ISO-8859-15','ISO-8859-16',
            'cp1252','latin-1'
        ];

        if (is_array($data)) {
            array_walk_recursive($data, function (&$value) use ($encodings) {
                foreach ($encodings as $encoding) {
                    if (mb_check_encoding($value, 'UTF-8')) return;
                    $value = mb_convert_encoding($value, 'UTF-8', $encoding);
                }
            });
        } else {
            foreach ($encodings as $encoding) {
                if (mb_check_encoding($data, 'UTF-8')) return;
                $data = mb_convert_encoding($data, 'UTF-8', $encoding);
            }
        }
    }

    /**
     * Imprime JSON formatado e encerra a execução
     */
    public static function echoFormatted($data)
    {
        self::formataJson($data);
        header('Content-Type: application/json');
        echo utf8_encode(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT, JSON_NUMERIC_CHECK));
        exit;
    }

    /**
     * Debug completo de qualquer variável
     */
    public static function gD($data)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];

        if (!is_object($data)) {
            $json = [
                'Path' => $file . " | line: " . $line,
                'Type data' => gettype($data),
                'data' => $data
            ];
        } else {
            $class = get_class($data);
            $json = [
                'Path' => $file . " | line: " . $line,
                'Class Name' => $class,
                'Debug backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
                'Method' => get_class_methods($class),
                'Attributes' => (array)$data,
                'Parent Extends' => get_parent_class($class) ?: 'Nenhuma',
                'Type data' => gettype($data),
                'Include files' => get_included_files(),
                'Required files' => get_required_files()
            ];
        }

        self::echoFormatted($json);
    }

    /**
     * Detecta e corrige codificação de array/string para UTF-8
     */
    public static function corrigirCodificacaoArray($dados)
    {
        foreach ($dados as $chave => $valor) {
            if (is_array($valor)) {
                $dados[$chave] = self::corrigirCodificacaoArray($valor);
            } elseif (is_string($valor)) {
                if (!mb_detect_encoding($valor, 'UTF-8', true)) {
                    $dados[$chave] = utf8_encode($valor);
                } else {
                    $dados[$chave] = mb_convert_encoding($valor, 'UTF-8', 'UTF-8');
                }
            }
        }
        return $dados;
    }

    /**
     * Gera Base64 URL-safe
     */
    public static function base64Encode($string)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    /**
     * Assina dados com chave privada
     */
    public static function signData($privateKey, $data, $hash = false)
    {
        if ($hash === true) {
            $data = hash('sha256', $data, true);
        }

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return self::base64Encode($signature);
    }

    public static function listParametersClassFunction($class, $function){
        $ref = new ReflectionMethod($class, $function);
        return $ref->getParameters();
    }

    /**
     * Encripta e desencripta campos com AES
     */
    public static function encriptar($valorEncriptar)
    {
        $AESKEY = "TemoraETT";
        return sprintf("HEX(AES_ENCRYPT('%s', '%s'))", $valorEncriptar, $AESKEY);
    }

    public static function desencriptar($nomeCampo)
    {
        $AESKEY = "TemoraETT";
        return 'CAST(AES_DECRYPT(UNHEX(' . $nomeCampo . '),"' . $AESKEY . '") AS CHAR(150))';
    }
}
