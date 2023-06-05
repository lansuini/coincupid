<?php
/**
 * Code2latlon.class.php
 *
 * - Get latitude/longitude of capital country by country code/id

 * REQUERIMENTS:
 *
 * - none

 * @author Dix醤 Santiesteban Feria
 * @author {@link https://facebook.com/dixan.sant}
 * @link https://www.linkedin.com/in/dixansant/
 * @since October 2021
 * @version 1.0.0
 * @license GNU General Public License v3.0
 */
namespace App\Http\Library;

define('BY_ID',		'BY_ID');
define('BY_CODE',	'BY_CODE');
define('EMPTY_STR',	'');

class Code2latlon
{
    const latlonSource = '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';
    public function get($code)
    {
        return $this->result($code);
    }

    public function getByIndex($id)
    {
        return $this->result($id, BY_ID);
    }

    private function result($v, $by = BY_CODE)
    {

        $v = strtoupper($v);
        $s = chr(124);
        $l = $s . ($by === BY_CODE ? $v : EMPTY_STR);
        $r = [];
        $pt = explode($l, bzdecompress(base64_decode(self::latlonSource)));
        if ($by === BY_CODE) @list(, $f) = $pt;
        else $f = substr($pt[intval($v)], 2);
        if (isset($f)) {
            list($i) = explode($s, $f);
            $m = explode(chr(58), $i);
            $r = array_reduce($m, function ($a, $s) {
                $num = array_reduce([[0, 3], [3, strlen($s) - 3]], function ($r, $p) use ($s) {
                    list($f, $l) = $p;
                    $z = hexdec(substr($s, $f, $l));
                    return $r === EMPTY_STR ? $z - 180 : "$r.$z";
                }, EMPTY_STR);
                return array_merge($a, [$num]);
            }, []);
        }
        return $r;
    }
}