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
     * Lista os parâmetros de uma função de uma classe
     */
    public static function listParametersClassFunction($class, $function){
        $ref = new ReflectionMethod($class, $function);
        return $ref->getParameters();
    }

    /**
     * Valida campos obrigatórios
     */
    public static function validateRequiredFields($bodyRequest, $requiredArguments)
    {
        foreach ($requiredArguments as $value) {
            if (!array_key_exists($value, $bodyRequest)) {
                self::emitirErro("Argumento obrigatório: {$value}", 422);
            }
            if (empty($bodyRequest[$value])) {
                self::emitirErro("Argumento {$value} obrigatório não pode ser vazio ", 422);
            }
        }
    }

    public static function jweEncripty($payload)
    {
        $publicKeyPem = file_get_contents('../config/keys/public.pem');

        $base64url = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };

        if (is_array($payload)) {
            $payload = json_encode($payload);
        }
        $aesKey = openssl_random_pseudo_bytes(32);
        $iv = openssl_random_pseudo_bytes(16);
        $cipherText = openssl_encrypt(
            $payload,
            'aes-256-cbc',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        $tag = hash_hmac('sha256', $cipherText, $aesKey, true);
        $encryptedKey = '';
        if (!openssl_public_encrypt($aesKey, $encryptedKey, $publicKeyPem, OPENSSL_PKCS1_OAEP_PADDING)) {
            Helper::emitirErro("Falha ao criptografar a chave AES com a chave pública RSA", "500 Internal Server Error");
        }

        $header = [
            'alg' => 'RSA-OAEP',
            'enc' => 'A256CBC-HS256'
        ];
        return implode('.', [
            $base64url(json_encode($header)),
            $base64url($encryptedKey),
            $base64url($iv),
            $base64url($cipherText),
            $base64url($tag)
        ]);
    }

    /**
     * Descriptografa um JWE
     */
    public static function jweDecripty($jwe)
    {
        $privateKeyPem = file_get_contents('../config/keys/private.pem');

        $base64url = function ($data) {
            return base64_decode(strtr($data, '-_', '+/'));
        };

        list($headerB64, $encryptedKeyB64, $ivB64, $cipherTextB64, $tagB64) = explode('.', $jwe);

        $header = json_decode($base64url($headerB64), true);
        $encryptedKey = $base64url($encryptedKeyB64);
        $iv = $base64url($ivB64);
        $cipherText = $base64url($cipherTextB64);
        $tag = $base64url($tagB64);

        $aesKey = '';
        if (!openssl_private_decrypt($encryptedKey, $aesKey, $privateKeyPem, OPENSSL_PKCS1_OAEP_PADDING)) {
            Helper::emitirErro("Falha ao decriptar a chave AES com a chave privada RSA", "500 Internal Server Error");
        }
        $calculatedTag = hash_hmac('sha256', $cipherText, $aesKey, true);
        if (!hash_equals($calculatedTag, $tag)) {
            Helper::emitirErro("Falha na verificação de integridade (HMAC não confere)", "500 Internal Server Error");
        }

        $payload = openssl_decrypt(
            $cipherText,
            'aes-256-cbc',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $decoded = json_decode($payload, true);
        return $decoded !== null ? $decoded : $payload;
    }
}
