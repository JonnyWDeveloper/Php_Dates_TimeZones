<?php
//Helpful error reporting while developing.
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'Date.php'; //Load the Date class once.
require_once 'Validator.php'; //Load the Validator class once.
$sign = '';
$hours = '';
$difference = '';
$error_msg = '';
$missing = null;
$errors = null;
$date1 = '';
$timezone1 = '';
$date2 = '';
$timezone2 = '';
$username = '';
$email = '';

########################################################################
# DATE AND TIMEZONE FUNCTIONS                                          #
########################################################################

/**
 * Gets the city name for the Time Zone.
 *
 * @param int $time_zone_const is a representation of a time zone
 * @param string $country_code is a two letter ISO country code
 * @return array ::listIdentifiers(what:, $country)
 *//*
function getDateZoneCity($time_zone_const, string $country_code)
{
    return DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code);
}*/

/**
 * Divides the GMT Offset in seconds, that come as an int value, by 3600 to get the hours.
 * @param Date $date
 * @return int the result is the offset in hours.
 */
function getOffsetHours(Date $date)
{
    $time_offset = $date->getOffset();
    return $time_offset / 3600;
}

/**
 * Divides the GMT Offset in seconds, that come as an int value, by 3600 to get the hours.
 * @param DateTime $date_time
 * @return int the result is the offset in hours.
 */
function getOffsetHoursDT(DateTime $date_time)
{
    return $date_time->getOffset() / 3600;
}

/**
 * Gets the city name from a DateTimeZone by using the explode function to split a string
 * at the '/' delimiter into an array of strings.
 * @param DateTimeZone $dtz
 * @return string $city_name[1] Returns the second field of the string array which is the name of the city
 * $city_name[0] represents the zone name.
 */
function getCityName(DateTimeZone $dtz)
{
    $city_name = explode('/', $dtz->getName());
    //Gives us the DateTimezone´s name pair.
    //We want only the city value - the string after the forward slash.
    return $city_name[1]; //Returns the city name as a string.
}

/**
 * Checks the city's offset hours
 * and returns the applicable offset sign +/-
 * @param int $city_offset_hours
 * @return string $sign a global variable that returns a plus/minus sign.
 */
function setCityOffsetSign(int $city_offset_hours)
{
    if ($city_offset_hours > 0) {
        global $sign; //It is necessary to declare a global variable.
        $sign = '<span class="plusText">+</span>';
    } elseif ($city_offset_hours === 0) {
        global $sign;
        $sign = '';
    } else {
        global $sign;
        $sign = '<span class="minusText">-</span> ';;
    }
    return $sign;
}

########################################################################
# THE FORM HAS BEEN POSTED -- VALIDATION TESTS FOLLOW                  #
########################################################################

// 1. Super Global Variable $_SERVER: the form has been posted.
// 2. Submit button 'display' has been pressed.
// 3. All fields must be set.
// 4. The fields will be validated against the Date & Validator classes.
// 5. Timezones will be created.
// 6. Date calculations will be made.
// 7. Results will be presented.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (filter_has_var(INPUT_POST, 'display')) {
        // Keep input data already submitted
        $date1 = strip_tags($_POST['Date1']);
        $timezone1 = strip_tags($_POST['Timezone1']);
        $date2 = strip_tags($_POST['Date2']);
        $timezone2 = strip_tags($_POST['Timezone2']);
        $username = strip_tags($_POST['Username']);
        $email = strip_tags($_POST['Email']);
        if (!empty (strip_tags($_POST['Date1']))
            && !empty(strip_tags($_POST['Timezone1']))
            && !empty(strip_tags($_POST['Date2']))
            && !empty(strip_tags($_POST['Timezone2']))
            && !empty(strip_tags($_POST['Username']))
            && !empty(strip_tags($_POST['Email']))) {
            try {
                $required = array('Date1', 'Timezone1', 'Date2', 'Timezone2', 'Username', 'Email');
                $val = new Validator($required);
                $val->checkTextLength('Date1', 10, 10);
                // The Regex pattern allows for som illegal dates to pass through
                // however will be filtered by the core checkdate method.
                $val->checkDate('Date1',
                    '/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
                    $_POST['Date1']);
                $val->noFilter('Timezone1');
                $val->checkTextLength('Date2', 10, 10);
                $val->checkDate('Date2',
                    '/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/',
                    $_POST['Date2']);
                $val->noFilter('Timezone2');
                $val->checkTextLength('Username', 3);
                $val->removeTags('Username');
                $val->isEmail('Email');
                $filtered = $val->validateInput();// The validated input is stored in $filtered.
                $missing = $val->getMissing();
                $errors = $val->getErrors();
                if (!$missing && !$errors) {
                    // Get the user input dates from the form (UI).
                    $input_date1 = strip_tags($_POST['Date1']);
                    $input_date2 = strip_tags($_POST['Date2']);
                    // Get the user input timezones from the form (UI).
                    // It is now safe to add the raw field values.
                    $input_timezone1 = strip_tags($_POST['Timezone1']);
                    $input_timezone2 = strip_tags($_POST['Timezone2']);
                    // Create DateTimeZones.
                    $dateTimeZone1 = new DateTimeZone($input_timezone1);
                    $dateTimeZone2 = new DateTimeZone($input_timezone2);
                    //Create DateTime objects.
                    $dateTime1 = new DateTime($input_date1, $dateTimeZone1);
                    $dateTime2 = new DateTime($input_date2, $dateTimeZone2);
                    $zone1 = getOffsetHoursDT($dateTime1);//What offset does the timezone have?
                    $print_sign1 = setCityOffsetSign($zone1);//+ Or -?
                    $zone2 = getOffsetHoursDT($dateTime2);
                    $print_sign2 = setCityOffsetSign($zone2);
                    // Returns how the SECOND date differs form the first,
                    // Is it +days or -days? AND/OR +hours or -hours?
                    // Is the SECOND date ahead or behind the first?
                    $interval = $dateTime1->diff($dateTime2);
                    $text = $interval->format('%R%h ');// %R is the same as:
                    // [invert] => 0 or 1 = + hours or - hours.

########################################################################
# CALCULATE AND DISPLAY THE RESULTS                                    #
########################################################################

                    //The result assumes that the two dates, entered by the user on the form,
                    // have the same time of day.
                    //The result box is confined within the <div> HTML5 element below.
                    echo '<h2>Comparison Result:</h2>';
                    echo '<div id="display">';
                    echo '<p>&nbsp;</p>';
                    $now = new DateTime();
                    $now->setTimezone($dateTimeZone1);  //Set the current time in the zone.
                    $print_hours1 = '';
                    $print_hours2 = '';
                    //This is done in order to avoid getting two minus signs, in case the timezone is negative to UTC/GMT.
                    //It is a color styling check also: a green plus? or a red minus?;
                    if ($zone1 < 0) {
                        $print_hours1 = '<span class="minusText">' . substr($zone1, 1) . '</span>';
                    } else {
                        $print_hours1 = '<span class="plusText">' . $zone1 . '</span>';
                    }
                    if ($zone2 < 0) {
                        $print_hours2 = getOffsetHoursDT($dateTime2);
                        $print_hours2 = '<span class="minusText">' . substr($zone2, 1) . '</span>';
                    } else {
                        $print_hours2 = '<span class="plusText">' . $zone2 . '</span>';
                    }
                    echo '<b>1. ' . $input_timezone1 . '</b><br>';
                    echo 'UTC/GMT: ' . $print_sign1 . $print_hours1 . '<br>';
                    echo $input_date1 . ' ' . $now->format("H:i:s") . '<br><br>';
                    $now->setTimezone($dateTimeZone2);   //Set the current time in the zone.
                    echo '<b>2. ' . $input_timezone2 . '</b><br>';
                    echo 'UTC/GMT: ' . $print_sign2 . $print_hours2 . '<br>';
                    echo $input_date2 . ' ' . $now->format("H:i:s") . '<br><br>';
                    $diff_days1 = Date::hourDiff($dateTime1, $dateTime2);
                    if ($diff_days1 < 0)//Date1 is behind in DAYS
                    {
                        if ($zone1 > $zone2) {
                            echo '<p><b>1. BEHIND in DAYS and AHEAD in TIMEZONE:</b></p>';
                            $zone = $zone1 - $zone2;
                            echo '<br>Zone (difference in hours): ' . $zone;
                            echo '<br> Days (in hours): ' . $diff_days1;
                            echo '<br>TOTAL: ' . '<span class="minus">' . getCityName($dateTimeZone1) . '</span> is  ' .
                                $total_hours = $diff_days1 + $zone . ' hour(s) behind <span class="plus">' . getCityName($dateTimeZone2) . '</span>';
                        } elseif ($zone1 == $zone2) {
                            echo '<p><b>1. BEHIND in DAYS and EQUAL in TIMEZONE:</b></p>';
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>TOTAL: ' . '<span class="minus">' . getCityName($dateTimeZone1) . '</span> is  ' .
                                $total_hours = $diff_days1 + $zone . ' hour(s) behind <span class="plus">' . getCityName($dateTimeZone2) . '</span>';
                        } else {
                            echo '<p><b>1. BEHIND in DAYS and BEHIND in TIMEZONE:</b></p>';
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>TOTAL: ' . '<span class="minus">' . getCityName($dateTimeZone1) . '</span> is  ' .
                                $total_hours = $diff_days1 + $zone . ' hour(s) behind <span class="plus">' . getCityName($dateTimeZone2) . '</span>';
                        }
                        echo '<p>&nbsp;</p>';
                    } elseif ($diff_days1 === 0) {//Dates are equal in DAYS.
                        if ($zone1 > $zone2) {
                            echo '<p><b>1. EQUAL in DAYS and AHEAD in TIMEZONE:</b></p>';
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>TOTAL: ' . '<span class="plus">' . getCityName($dateTimeZone1) . '</span> is ' .
                                $total_hours = $zone - $diff_days1 . ' hour(s) ahead of <span class="minus">' . getCityName($dateTimeZone2) . '</span>';
                        } elseif ($zone1 == $zone2) {
                            echo '<p><b>1. EQUAL in DAYS and EQUAL in TIMEZONE:</b></p>';
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>TOTAL: ' . '<span class="plus">' . getCityName($dateTimeZone1) . '</span> is ' .
                                $total_hours = $zone - $diff_days1 . ' hour(s) and equal to <span class="minus">' . getCityName($dateTimeZone2) . '</span>';
                        } else {
                            echo '<p><b>1. EQUAL in DAYS and BEHIND in TIMEZONE:</b></p>';
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>TOTAL: ' . '<span class="minus">' . getCityName($dateTimeZone1) . '</span> is ' .
                                $total_hours = $zone - $diff_days1 . ' hour(s) behind <span class="plus">' . getCityName($dateTimeZone2) . '</span>';
                        }
                        echo '<p>&nbsp;</p>';
                    } elseif ($diff_days1 > 0) {//1. ahead in DAYS
                        if ($zone1 > $zone2) {//ZONE1 > ZONE2
                            echo '<p><b>1. AHEAD in DAYS and AHEAD in TIMEZONE:</b></p>';
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>TOTAL: ' . '<span class="plus">' . getCityName($dateTimeZone1) . '</span> is ' .
                                $total_hours = $diff_days1 + $zone . ' hour(s) ahead of <span class="minus">' . getCityName($dateTimeZone2) . '</span>';

                        } elseif ($zone1 == $zone2) {//EQUAL Zone
                            echo '<p><b>1. AHEAD in DAYS & EQUAL in TIMEZONE:</b></p>';
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>TOTAL: ' . '<span class="plus">' . getCityName($dateTimeZone1) . '</span> is ' .
                                $total_hours = $diff_days1 + $zone . ' hour(s) ahead of <span class="minus">' . getCityName($dateTimeZone2) . '</span>';
                        } else {//ZONE1 < ZONE2
                            echo '<p><b>1. AHEAD in DAYS and BEHIND in TIMEZONE:</b></p>';
                            echo '<br>Zone (difference in hours): ' . $zone = $zone1 - $zone2;
                            echo '<br>Days (in hours): ' . $diff_days1;
                            echo '<br>TOTAL: ' . '<span class="plus">' . getCityName($dateTimeZone1) . '</span> is ' .
                                $total_hours = $diff_days1 + $zone . ' hour(s) ahead of <span class="minus">' . getCityName($dateTimeZone2) . '</span>';
                        }
                        echo '<p>&nbsp;</p>';
                    }
                    echo ' </div ><p >&nbsp;</p > ';
                }
            } catch
            (Exception $e) {
                echo $e;
            }
        } else { // The user has failed to fill in all of the fields.
            // A detailed error message will be displayed in darkred.
            try {
                $required = array('Date1', 'Timezone1', 'Date2', 'Timezone2', 'Username', 'Email');
                $val = new Validator($required);
                // This is only done because a filter is
                // necessary for each field to make the validateInput() method work.
                $val->noFilter('Date1');
                $val->noFilter('Date2');
                $val->noFilter('Timezone1');
                $val->noFilter('Timezone2');
                $val->noFilter('Username');
                $val->noFilter('Email');
                $filtered = $val->validateInput();// The validated input is stored in $filtered.
                $missing = $val->getMissing();
                $errors = $val->getErrors();
            } catch
            (Exception $e) {
                echo $e;
            }
        }
    }
}
?>
