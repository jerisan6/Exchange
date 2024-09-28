<?php

namespace App\Constants;

class GlobalConst {
    const USER_PASS_RESEND_TIME_MINUTE = "1";

    const ACTIVE                = true;
    const BANNED                = false;
    const DEFAULT_TOKEN_EXP_SEC = 3600;

    const VERIFIED      = 1;
    const APPROVED      = 1;
    const PENDING       = 2;
    const REJECTED      = 3;
    const DEFAULT       = 0;
    const UNVERIFIED    = 0;

    const USER      = "USER";
    const ADMIN = "ADMIN";

    const INSIDE_WALLET     = "Inside Wallet";
    const OUTSIDE_WALLET     = "Outside Wallet";

 
    const STATUS_PENDING             = 1;
    const STATUS_CONFIRM_PAYMENT     = 2;
    const STATUS_CANCEL              = 3;
    const STATUS_REJECT              = 4;
    
    const STATUS_ALL                 = "ALL";

    const UNKNOWN       = "UNKNOWN";

    const USEFUL_LINK_PRIVACY_POLICY = "PRIVACY_POLICY";
}