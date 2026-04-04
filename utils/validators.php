<?php

function isValidEmailStrict($email) {
    // 1. Basic syntax check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // 2. Convert to lowercase
    $email = strtolower($email);

    // 3. Allow only Gmail (you can modify later)
    if (!str_ends_with($email, '@gmail.com')) {
        return false;
    }

    // 4. Block common typos
    $blockedDomains = ['gma.com', 'gmial.com', 'gamil.com'];

    $domain = substr(strrchr($email, "@"), 1);

    if (in_array($domain, $blockedDomains)) {
        return false;
    }

    // 5. Check MX record (domain exists or not)
    if (!checkdnsrr($domain, "MX")) {
        return false;
    }

    return true;
}