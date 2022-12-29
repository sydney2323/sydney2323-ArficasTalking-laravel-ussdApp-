<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Traits\UssdMenuTrait;
use App\Http\Traits\SmsTrait;

class UssdController extends Controller
{
    use UssdMenuTrait, SmsTrait;

    public function ussdRequestHandler(Request $request)
    {
        $sessionId   = $request["sessionId"];
        $serviceCode = $request["serviceCode"];
        $phone       = $request["phoneNumber"];
        $text        = $request["text"];

        header('Content-type: text/plain');

        if(User::where('phone', $phone)->exists()){
            // Function to handle already registered users
            $this->handleReturnUser($text, $phone);
        }else {
             // Function to handle new users
             $this->handleNewUser($text, $phone);
        }
    } 

        
  public function handleNewUser($ussd_string, $phone)
  {
        $ussd_string_exploded = explode ("*",$ussd_string);

        // Get menu level from ussd_string reply
        $level = count($ussd_string_exploded);

        if(empty($ussd_string) or $level == 0) {
            $this->newUserMenu(); // show the home menu
        }

        switch ($level) {
            case ($level == 1 && !empty($ussd_string)):
                if ($ussd_string_exploded[0] == "1") {
                    // If user selected 1 send them to the registration menu
                    $this->ussd_proceed("Please enter your full name and desired pin separated by commas. \n eg: Jane Doe,1234");
                } else if ($ussd_string_exploded[0] == "2") {
                    //If user selected 2, send them the information
                    $this->ussd_stop("You will receive more information on SampleUSSD via sms shortly.");
                    $this->sendText("This is a subscription service from SampleUSSD.",$phone);
                } else if ($ussd_string_exploded[0] == "3") {
                    //If user selected 3, exit
                    $this->ussd_stop("Thank you for reaching out to SampleUSSD.");
                }
            break;
            case 2:
                if ($this->ussdRegister($ussd_string_exploded[1], $phone) == "success") {
                    $this->servicesMenu();
                }
            break;
            // N/B: There are no more cases handled as the following requests will be handled by return user
        }
    }

    public function handleReturnUser($ussd_string, $phone)
	{ 
		$ussd_string_exploded = explode ("*",$ussd_string);

		// Get menu level from ussd_string reply
		$level = count($ussd_string_exploded);

		if(empty($ussd_string) or $level == 0) {
			$this->returnUserMenu(); // show the home/first menu
		}

		switch ($level) {
			case ($level == 1 && !empty($ussd_string)):
				if ($ussd_string_exploded[0] == "1") {
					// If user selected 1 send them to the login menu
					$this->ussd_proceed("Kindly input your pin");
				} else if ($ussd_string_exploded[0] == "2") {
					//If user selected 2, end session
					$this->ussd_stop("Thank you for reaching out to SampleUSSD.");
				} else {
					$this->ussd_stop("Invalid Input");
				}
			break;
			case 2:
				if ($this->ussdLogin($ussd_string_exploded[1], $phone) == "Success") {
					$this->servicesMenu();
				}
			break;
			case 3:
				if ($ussd_string_exploded[2] == "1") {                   
					$this->ussd_stop("You will receive an sms shortly.");
					$this->sendText("You have successfully subscribed to updates from SampleUSSD.",$phone);
				} else if ($ussd_string_exploded[2] == "2") {
					$this->ussd_stop("You will receive more information on SampleUSSD via sms shortly.");
					$this->sendText("This is a subscription service from SampleUSSD.",$phone);
				} else if ($ussd_string_exploded[2] == "3") {
					$this->ussd_stop("Thanks for reaching out to SampleUSSD.");              
				} else {
					$this->ussd_stop("Invalid input!");
				}
			break;
		}
	}

    public function ussd_proceed($ussd_text) {
         echo "CON $ussd_text";
    }
    public function ussd_stop($ussd_text) {
         echo "END $ussd_text";
    }

         /*
     * Handles USSD Registration Request
    */
    public function ussdRegister($details, $phone)
    {
        $input = explode(",",$details);//store input values in an array
        $full_name = $input[0];//store full name
        $pin = $input[1];        
       
        $user = new User;
        $user->name = $full_name;
        $user->phone = $phone;
        // You should encrypt the pin
        $user->pin = $pin;
        $user->save();
 
        return "success";
    }
 
    /**
     * Handles Login Request
     */
    public function ussdLogin($details, $phone)
    {
        $user = User::where('phone', $phone)->first();

        if ($user->pin == $details ) {
            return "Success";           
        } else {
            return $this->ussd_stop("Login was unsuccessful!");
        }
    }
}
