<?php
/*
$x = '{
    "ORG_NAME": "a",
    "ORG_DETAILS": "b",
    "REQ_BY_EMAIL_ID": "c"
}';
$y = json_decode($x);
echo $y->ORG_NAME;
*/
$x = '{
    "COMMAND": "ADD",
    "PARAM": {
        "ORG_NAME": "wrench",
        "ORG_DETAILS": "project control solutions",
        "REQ_BY_EMAIL_ID": "aju.peter@outlook.com"
    }
}';
    $y = json_decode($x);
echo $y->COMMAND;
