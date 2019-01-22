<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
 
 function ipCIDRCheck ($IP, $CIDR) {
    list ($net, $mask) = explode("/", $CIDR);
    
    $ip_net = ip2long ($net);
    $ip_mask = ~((1 << (32 - $mask)) - 1);

    $ip_ip = ip2long ($IP);

    $ip_ip_net = $ip_ip & $ip_mask;

    return ($ip_ip_net == $ip_net);
}

// converts inet_pton output to string with bits
function inet_to_bits($inet) 
{
// pack and unpack behavior was changed in PHP 5.5
// this call should be OK w/o changes, though
// http://php.net/manual/en/migration55.incompatible.php
   $unpacked = unpack('A16', $inet); // working with PHP 5.4
   $unpacked = str_split($unpacked[1]);
   $binaryip = '';
   foreach ($unpacked as $char) {
             $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
   }
   return $binaryip;
}    

function isIPv4($ip) {
    return preg_match('/([0-9]{1,3}\.){3}[0-9]{1,3}/', $ip);
}

function isIPv6($ip) {
    return preg_match('/([0-9A-Fa-f]{4}:){7}[0-9A-Fa-f]{4}/', $ip);
}

function isCIDR($ip) {
    $ip = explode("/", $ip);
    if ( count($ip) == 2 ) {
        return isIPv4($ip[0]) && is_numeric($ip[1]) && $ip[1] >= 0 && $ip[1] <=32;
    } else return false;
}

function isCIDR6($ip) {
    $ip = explode("/", $ip);
    if ( count($ip) == 2 ) {
        return isIPv6($ip[0]) && is_numeric($ip[1]) && $ip[1] >= 0 && $ip[1] <=128;
    } else return false;
}

function ipCIDRCheck6($ip, $cidrnet) {
    //$ip='21DA:00D3:0000:2F3B:02AC:00FF:FE28:9C5A';
    //$cidrnet='21DA:00D3:0000:2F3B::/64';

    $ip = inet_pton($ip);
    $binaryip = inet_to_bits($ip);

    list($net, $maskbits) = explode('/', $cidrnet);
    $net = inet_pton($net);
    $binarynet = inet_to_bits($net);

    $ip_net_bits = substr($binaryip, 0, $maskbits);
    $net_bits = substr($binarynet, 0, $maskbits);

    return ( $ip_net_bits === $net_bits );
}
?>
