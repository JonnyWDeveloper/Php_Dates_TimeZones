<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Date & Validator Classes - OOP - PHP</title>
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
//Helpful error reporting while developing.
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Date_Class_Logic.php';
?>
<h2>Compare Dates and Time Zones all over the world!</h2>
<form id="date_form" action="" method="post">
    <?php
    if ($missing) {
        echo '<div class="logic"><p class="minusText">The following required fields have not been filled in:</p>';
        echo '<ul>';
        foreach ($missing as $field) {
            echo '<li class="minusText">' . $field . '</li>';
        }
        echo '</ul><p>&nbsp;</p></div>';
    } ?>
    <label><b><span style="color: #ff0000">*</span>&nbsp;An asterisk indicates a required field</b></label>
    <br><br>
    <label for="Date1"><b><span style="color: #ff0000">*</span>&nbsp;Date 1: </b><span class="plusText" style="font-size:small">YYYY-MM-DD</span>
        <?php
        if (isset($errors['Date1'])) {
            echo '<span class="minusText">' . $errors['Date1'] . '</span>';
        }
        ?></label>
    <input id="Date1" name="Date1" type="text" placeholder=""
           value="<?php echo htmlspecialchars($date1); ?>"/>
    <label for="Timezone1"><b><span style="color: #ff0000">*</span>&nbsp;Timezone 1:</b>
        <?php
        if (isset($errors['Timezone1'])) {
            echo '<span class="minusText">' . $errors['timezone1'] . '</span>';
        }
        ?></label>

    <select id="Timezone1" name="Timezone1">
        <?php
        echo '<option></option>';
        $countries = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($countries as $country)
            if ($country !== 'UTC') {
                echo '<option value="' . $country . '"';
                if ($timezone1 === $country) {
                    echo " selected";
                }
                echo ">" . $country . "</option>";
            }
        ?>
    </select>
    <label for="Date2"><b><span style="color: #ff0000">*</span>&nbsp;Date 2: </b><span class="plusText" style="font-size:small">YYYY-MM-DD</span>
        <?php
        if (isset($errors['Date2'])) {
            echo '<span class="minusText">' . $errors['Date2'] . '</span>';
        }
        ?></label>
    <input id="Date2" name="Date2" type="text" placeholder=""
           value="<?php echo htmlspecialchars($date2); ?>"/>
    <label for="timezone2"><b><span style="color: #ff0000">*</span>&nbsp;Timezone 2: </b>
        <?php
        if (isset($errors['Timezone2'])) {
            echo '<span class="minusText">' . $errors['Timezone2'] . '</span>';
        }
        ?></label>
    <select id="Timezone2" name="Timezone2">
        <?php
        echo '<option></option>';
        $countries = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($countries as $country)
            if ($country !== 'UTC') {
                echo '<option value="' . $country . '"';
                if ($timezone2 === $country) {
                    echo " selected";
                }
                echo ">" . $country . "</option>";
            }
        ?>
    </select>
    <p><label for="Username"><b><span style="color: #ff0000">*</span>&nbsp;Username: </b><span class="plusText"
                                                style="font-size:small">3 characters minimum</span>
            <?php
            if (isset($errors['Username'])) {
                echo '<span class="minusText">' . $errors['Username'] . '</span>';
            }
            ?></label> <input name="Username" type="text" id="Username"
                              value="<?php echo htmlspecialchars($username); ?>"/></p>
    <p><label for="email"><b><span style="color: #ff0000">*</span>&nbsp;Email:</b>
            <?php
            if (isset($errors['Email'])) {
                echo '<span class="minusText">' . $errors['Email'] . '</span>';
            }
            ?></label> <input name="Email" type="text" id="Email"
                              value="<?php echo htmlspecialchars($email); ?>"/></p>
        <input style="font-size: smaller" type="submit" id="clear" name="clear" value="Clear">
    <br>
    <input style="font-size: large" name="display" type="submit" value="Show">
    <p><?php echo $error_msg; ?></p>
</form>
</body>
</html>