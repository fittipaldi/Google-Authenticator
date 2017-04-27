<?php
/**
 * Google Authenticator
 *
 * @package  GoogleAuthenticator
 * @author   Fittipaldi <fittipaldi.gustavo@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/fittipaldi/Google-Authenticator
 */

namespace GoogleAuthenticator;

use Base32\Base32;
use Couchbase\Exception;

/**
 * Class GoogleAuthenticator
 *
 * @author   Fittipaldi <fittipaldi.gustavo@gmail.com>
 * @package  GoogleAuthenticator
 */
class GoogleAuthenticator
{
    const API_URL = 'https://chart.googleapis.com/chart?chs={chs}&chld=M|0&cht=qr&chl={chl}';
    const CODE_LENGTH = 6;
    const SECRET_LENGTH = 16;

    protected $secretKey = null;

    protected $base32Chars = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '='
    );

    /**
     * @param  null $secretKey
     * @throws Exception
     */
    public function __construct($secretKey = null)
    {
        $this->secretKey = $secretKey;
        if (!$this->secretKey) {
            $this->secretKey = $this->generateSecretKey();
        }
        if (static::SECRET_LENGTH != strlen($this->secretKey) || 0 != count(array_diff(str_split($this->secretKey), $this->base32Chars))) {
            throw new Exception('Invalid secret key');
        }
    }

    /**
     * @return string
     */
    public function generateSecretKey()
    {
        $base32Chars = $this->base32Chars;
        unset($base32Chars[32]);
        $secretKey = '';
        for ($i = 0; $i < static::SECRET_LENGTH; $i++) {
            $secretKey .= $base32Chars[array_rand($base32Chars)];
        }
        return $secretKey;
    }

    /**
     * @param  string $appName
     * @param  int $size
     * @return mixed
     */
    public function getQRCodeUrl($appName, $size = 200)
    {
        if (!$this->secretKey) {
            $this->secretKey = $this->generateSecretKey();
        }
        return str_replace(
            array('{chs}', '{chl}'),
            array($size . 'x' . $size, urlencode('otpauth://totp/' . $appName . '?secret=' . $this->secretKey)),
            static::API_URL
        );
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param  string $code
     * @param  int $range
     * @param  int $rangePerSecond
     * @return bool
     */
    public function verifyCode($code, $range = 1, $rangePerSecond = 30)
    {
        $timeIndex = $this->getTimeIndex($rangePerSecond);
        for ($i = -$range; $i <= $range; $i++) {
            if (((string)$code) === $this->getCode($timeIndex + $i)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param  int $timeIndex
     * @return string
     */
    public function getCode($timeIndex = null)
    {
        if (is_null($timeIndex)) {
            $timeIndex = $this->getTimeIndex();
        }
        $secretkey = Base32::decode($this->secretKey);
        $hm = hash_hmac('SHA1', chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeIndex), $secretkey, true);
        $value = unpack('N', substr($hm, ord(substr($hm, -1)) & 0x0F, 4));
        $value = $value[1] & 0x7FFFFFFF;
        return str_pad($value % pow(10, static::CODE_LENGTH), static::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * @param int $rangePerSecond
     * @return int
     */
    public function getTimeIndex($rangePerSecond = 30)
    {
        return floor(time() / $rangePerSecond);
    }
}
