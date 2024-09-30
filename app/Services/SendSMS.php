<?php

namespace App\Services;

use App\Models\User;

class SendSMS
{
    public function shortListSMS(User $user)
    {
        $fullName = $user->surname . ' ' . $user->firstname . ' ' . $user->m_name;

        $programme_name = $user->programme->name;
        $course_name = $user->proposedCourse->course->name;
        // Initialize variables ( set your variables here )

        $username = config('services.nigeriabulksms.username');

        $password = config('services.nigeriabulksms.password');

        $sender = config('services.nigeriabulksms.sender');

        $message =  $fullName . '. You have  been offered provisional  admission to study  ' . $programme_name . ' ' . $course_name . ' at WUFEDPOLY. Kindly login to your account to generate remita for payment of Acceptance Fees and print your offer. After printing your offer, proceed to the Directorate of Higher Studies at WUFEDPOLY with your original credentials for physical screening';

        // Separate multiple numbers by comma

        $mobiles = $user->phone;

        // Set your domain's API URL

        $api_url = 'http://portal.nigeriabulksms.com/api/';

        //Create the message data

        $data = array('username' => $username, 'password' => $password, 'sender' => $sender, 'message' => $message, 'mobiles' => $mobiles);

        //URL encode the message data

        $data = http_build_query($data);

        //Send the message

        $ch = curl_init(); // Initialize a cURL connection

        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);

        $result = json_decode($result);

        if (isset($result->status) && strtoupper($result->status) == 'OK') {
            // Message sent successfully, do anything here

            return  'Message sent at N' . $result->price;
        } elseif (isset($result->error)) {
            // Message failed, check reason.

            return 'Message failed - error: ' . $result->error;
        } else {
            // Could not determine the message response.

            return 'Unable to process request';
        }





        curl_close($ch); // Close the cURL connection

    }
}
