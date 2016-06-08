<?php
/**
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com> 
 * Date: 16-6-8
 * Time: 下午3:31
 */

namespace Baykier\Identity;

use \DateTime;

/**
 * Class Identity
 * @验证是否合法的身份证
 * @refer https://www.drupal.org/project/chinese_identity_card
 * @package Baykier\Identity
 */
class Identity
{
    /**
     * @身份证地区
     * @var array
     */
    protected static $city = array(
        11 => "北京",
        12 => "天津",
        13 => "河北",
        14 => "山西",
        15 => "内蒙古",
        21 => "辽宁",
        22 => "吉林",
        23 => "黑龙江",
        31 => "上海",
        32 => "江苏",
        33 => "浙江",
        34 => "安徽",
        35 => "福建",
        36 => "江西",
        37 => "山东",
        41 => "河南",
        42 => "湖北",
        43 => "湖南",
        44 => "广东",
        45 => "广西",
        46 => "海南",
        50 => "重庆",
        51 => "四川",
        52 => "贵州",
        53 => "云南",
        54 => "西藏",
        61 => "陕西",
        62 => "甘肃",
        63 => "青海",
        64 => "宁夏",
        65 => "新疆",
        71 => "台湾",
        81 => "香港",
        82 => "澳门",
        91 => "国外",
    );

    /**
     * @验证是否合法身份证号
     * @param null $identityCart
     * @return bool
     */
    public static function validate($identityCart = null)
    {
        $idCardLength = strlen($identityCart);

        // Length checking.
        if (!preg_match('/^\d{17}(\d|x)$/i', $identityCart) and !preg_match('/^\d{15}$/i', $identityCart)) {
            return FALSE;
        }

        // Area checking.
        $cityCode = array_keys(self::$city);
        if (!in_array(intval(substr($identityCart, 0, 2)), $cityCode)) {
            return FALSE;
        }
        // 15bits card checks the birthday. and convert 18bits.
        if ($idCardLength == 15) {
            $year = '19' . substr($identityCart, 6, 2);
            $month = substr($identityCart, 8, 2);
            $day = substr($identityCart, 10, 2);
            if (!checkdate($month, $day, $year)) {
                return FALSE;
            }
            $sBirthday = $year . '-' . $month . '-' . $day;

            $d = new DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if ($sBirthday != $dd) {
                return FALSE;
            }
            // 15 to 18.
            $identityCart = substr($identityCart, 0, 6) . "19" . substr($identityCart, 6, 9);
            // Calculate the checksum of 18bits card.
            $bit_18 = self::getVerifyCardBit($identityCart);
            $identityCart = $identityCart . $bit_18;
        }
        // Checking whether the year bigger than 2078, and less than 1900.
        $year = substr($identityCart, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return FALSE;
        }

        // Handle 18bit card.
        $sBirthday = substr($identityCart, 6, 4) . '-' . substr($identityCart, 10, 2) . '-' . substr($identityCart, 12, 2);
        $d = new DateTime($sBirthday);
        $dd = $d->format('Y-m-d');
        if ($sBirthday != $dd) {
            return FALSE;
        }

        // Checking chinese identity card standard.
        $identityCartBase = substr($identityCart, 0, 17);
        if (strtoupper(substr($identityCart, 17, 1)) != self::getVerifyCardBit($identityCartBase)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @获取身份证最后一位校验码
     * @param null $identityCardBase
     * @refer
     * @return bool
     */
    protected static function getVerifyCardBit($identityCardBase = null)
    {
        if (strlen($identityCardBase) != 17) {
            return FALSE;
        }
        // Weighting factor.
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // Check code corresponding to the value.
        $verifyNumberList = array(
            '1',
            '0',
            'X',
            '9',
            '8',
            '7',
            '6',
            '5',
            '4',
            '3',
            '2',
        );
        $checksum = 0;
        for ($i = 0; $i < strlen($identityCardBase); $i++) {
            $checksum += substr($identityCardBase, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verifyNumber = $verifyNumberList[$mod];
        return $verifyNumber;
    }
}