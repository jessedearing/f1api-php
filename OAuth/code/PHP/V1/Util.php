<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Util
 *
 * @author jsingh
 */
class Util {
    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('Util','urlencode_rfc3986'), $input);
        } else if (is_scalar($input)) {
            return str_replace('+', ' ',
                str_replace('%7E', '~', rawurlencode($input)));
        } else {
            return '';
        }
    }

 /**
   * parses the url and rebuilds it to be
   * scheme://host/path
   */
    public function get_normalized_http_url($http_url) {
        $parts = parse_url($http_url);

        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
            || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    public static function get_port($http_url) {
        $parts = parse_url($http_url);
        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $port or $port = ($scheme == 'https') ? '443' : '80';
        return $port;
    }

     public static function get_host_name($http_url) {
        $parts = parse_url($http_url);
        $host = $parts['host'];
        return $host;
     }

     public static function getGuid() {

         // The field names refer to RFC 4122 section 4.1.2

         return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
             mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
             mt_rand(0, 65535), // 16 bits for "time_mid"
             mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
             bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
             // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
             // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
             // 8 bits for "clk_seq_low"
             mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
         );
     }

     public static function findComValue($comarray, $comidtype) {
         $return = false;
         for ($i = 0; $i < count($comarray); $i++) {
             if ($comarray[$i]->communicationTypeID == $comidtype){
                 $return = true;
                 break;
             }
         }
         
         return $return;
     }

     public static function formatBirthdateForSave($month, $day, $year) {
         if ($month != '') {
            return $year.'-'.$month .'-'.str_pad($day, 2, '0', STR_PAD_LEFT).'T00:00:00';
         }
         else {
            return null;
         }
         
     }

     public static function formatBirthdate($birthdate) {
        if ($birthdate != ''){
          $datearray = split("-", $birthdate);
            if (count($datearray) === 3) {
                $day =  split('T', $datearray[2]);
                $ret = array();
                $ret[0] = $datearray[1];
                $ret[1] = $day[0];
                $ret[2] = $datearray[0];
                
                return $ret;
            }
            else {
                return $birthdate;
            }
        }
     }
}
?>
