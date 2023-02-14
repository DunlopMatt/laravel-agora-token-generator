<?php

namespace App\Http\Controllers;

use App\Components\Services\Agora\RtcTokenBuilder;
use App\Components\Services\Agora\RtmTokenBuilder;
use Illuminate\Http\Request;

use DateTime;
use DateTimeZone;
use stdClass;

class TokenGenerator extends Controller
{
    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID');
        $this->appCertificate = env('AGORA_APP_CERTIFICATE');

        $expireTimeInSeconds = 36000;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $this->privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
    }


    public function generateLink(Request $request)
    {
        $channelName = $request->query('channel');
        $user = $request->query('user');

        $rtcRole = RtcTokenBuilder::RoleAttendee;
        $rtmRole = RtmTokenBuilder::RoleRtmUser;

        $rtcToken = RtcTokenBuilder::buildTokenWithUserAccount($this->appId, $this->appCertificate, $channelName, $user, $rtcRole, $this->privilegeExpiredTs);

        $rtmToken = RtmTokenBuilder::buildToken($this->appId, $this->appCertificate, $user, $rtmRole, $this->privilegeExpiredTs);

        $array = array(
            'rtcToken' => $rtcToken,
            'rtmToken' => $rtmToken,
        );

        $tokens = json_encode($array);

        $base64 = base64_encode($tokens);

        $link = join('/', ['video', $channelName, $base64]);

        return $link;

    }
}
