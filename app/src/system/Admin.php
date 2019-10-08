<?php

/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 9/25/2019
 * Time: 2:27 PM
 */
include_once dirname(__FILE__) . '/vendor/autoload.php';
use \PHPMailer\PHPMailer\PHPMailer;
use Firebase\JWT\JWT;
class Admin extends System
{
    public function __construct()
    {
        return parent::__construct();
    }

    public function validateParameter($fieldName, $value, $dataType, $required = true)
    {
        return parent::validateParameter($fieldName, $value, $dataType, $required); // TODO: Change the autogenerated stub
    }

    public function escape_data($data)
    {
        return parent::escape_data($data); // TODO: Change the autogenerated stub
    }

    public function login()
    {
        try{
            $email = $this->getEmail();
            $sql = "SELECT `id`,`password` FROM `admin` WHERE `email`='$email'";
            $qry = mysqli_query($this->con, $sql);
            if (mysqli_num_rows($qry) == 1){
                $row = mysqli_fetch_assoc($qry);
                $hash = $row['password'];
                if(password_verify($this->getPassword(), $hash)){
                    $paylod = [
                        'iat' => time(),
                        'iss' => 'localhost',
                        'exp' => time() + (60*60*8),
                        'userId' => $row['id']
                    ];

                    $token = JWT::encode($paylod, SECRETE_KEY);
                    $data = array('success' => true, 'statusCode' => SUCCESS_RESPONSE, 'message'=> 'Login successful','token'=>$token);
                    return $data;

                }else{
                    $data = array('success' => false, 'statusCode' => UNAUTHORISED, 'error'=> array('type' => "LOGIN_ERROR", 'message' => 'Invalid Login Credentials'));
                    return $data;
                }
            }else{
                $data = array('success' => false, 'statusCode' => NOT_FOUND, 'error'=> array('type' => "LOGIN_ERROR", 'message' => 'Account not found'));
                return $data;
            }
        }catch (\Exception $e){
            $data = array('success' => false, 'statusCode' => INTERNAL_SERVER_ERROR, 'error'=> array('type' => "SERVER_ERROR", 'message' => $e->getMessage()));
            return $data;
        }

    }

    public function create(){

        $cat = $this->getCategory();
        $email = $this->getEmail();
        $pwd = password_hash($this->getPassword(), PASSWORD_BCRYPT, array("cost" => 10));
        $permissions = $this->getPermission();
        $sql = "INSERT INTO `admin`(`id`, `email`, `password`, `permisions`, `name`, `surname`, `profile`, `category`) VALUES ('','$email','$pwd','$permissions','','','','$cat')";
        $qry = mysqli_query($this->con, $sql);

        if ($qry){
            //send email about creation to $email
            $data = array('success' => true, 'statusCode' => CREATED, 'message'=> 'Account created successfully');
            return $data;
        }else{
            $data = array(
                'success' => false,
                'statusCode' => INTERNAL_SERVER_ERROR,
                'error' => array(
                    'type' => "SERVER_ERROR",
                    'message' => 'Account creation not complete. Error: '. mysqli_error($this->con))
            );

            return $data;
        }

    }

    public function edit(){

    }

    public function updatePassword(){

    }

    public function adminAll(){
        try{
            $sql = "SELECT * FROM `admin` WHERE 1";
            $qry = mysqli_query($this->con, $sql);
            if (mysqli_num_rows($qry) > 0){
                $results = array();
                while($row = mysqli_fetch_assoc($qry)){
                    $arr = array(
                        'name' => $row['name'],
                        'surname' => $row['surname'],
                        'email' => $row['email'],
                        'profile' => $row['profile'],
                        'category' => $row['category'],
                        'permission' => $row['permisions']
                    );
                    array_push($results ,$arr);
                }
                $data = array('success' => true, 'statusCode' => SUCCESS_RESPONSE, 'message'=> 'Fetched Users','users'=>$results, 'results' => mysqli_num_rows($qry));
                return $data;
            }else{
                $data = array('success' => false, 'statusCode' => NOT_FOUND, 'error'=> array('type' => "FETCH_DATA_ERROR", 'message' => 'Users not found'));
                return $data;
            }
        }catch (\Exception $e){
            $data = array('success' => false, 'statusCode' => INTERNAL_SERVER_ERROR, 'error'=> array('type' => "SERVER_ERROR", 'message' => $e->getMessage()));
            return $data;
        }
    }

    public function membersAll(){
        try{

            $sql = "SELECT * FROM `members`";
            $qry = mysqli_query($this->con, $sql);

            if (mysqli_num_rows($qry) > 0){

                $details = array();
                while ($row = mysqli_fetch_assoc($qry)){
                    $id = $row['id'];

                    $psql = "SELECT `package` FROM `member_details` WHERE `member_id` = '$id'";
                    $pqry = mysqli_query($this->con, $psql);
                    $prs = mysqli_fetch_assoc($pqry);
                    $ssql = "SELECT  * FROM `dependant` WHERE `member_id` = '$id'";
                    $qqry = mysqli_query($this->con, $ssql);

                    $dependant = array();
                    while($rows = mysqli_fetch_assoc($qqry)){
                        $dep = array(
                            'registered' => $rows['created'],
                            'name' => $rows['name'],
                            'surname' => $rows['surname'],
                            'membership-number' => $rows['membership_no'],
                            'national-ID' => $rows['national_ID'],
                            'D.O.B' => $rows['dob'],
                            'gender' => $rows['gender']
                        );

                        array_push($dependant, $dep);
                    }

                    $member = array(
                        'id' => $id,
                        'name' => $row['name'],
                        'surname' => $row['surname'],
                        'national-ID' => $row['id_number'],
                        'membership-number' => $row['membership_no'],
                        'D.O.B' => $row['dob'],
                        'gender' => $row['gender'],
                        'address' => $row['address'],
                        'town' => $row['town'],
                        'package' => $this->packageName($prs['package']),
                        'registered' => $this->registrationDate($id),
                        'subscription' => $this->subscription($id),
                        'dependants' => $dependant
                    );

                    array_push($details, $member);

                }
                $data = array(
                    'success' => true,
                    'statusCode' => SUCCESS_RESPONSE,
                    'member' => $details
                );
                return $data;
            }else{
                $data =  array(
                    'success' => false,
                    'statusCode' => SUCCESS_RESPONSE,
                    'error' => array('type' => 'DATA_ERROR', 'message' => 'No data found')
                );
                return $data;
            }
        }catch (\Exception $exception){

            return array(
                'success' => false,
                'statusCode' => INTERNAL_SERVER_ERROR,
                'error' => array('type' => 'PROCESS_SERVER_ERROR', 'message' => $exception->getMessage())
            );

        }
    }

    public function subscription($member){

        $sql = "SELECT * FROM `subscriptions` WHERE `member_id` = '$member' AND now() BETWEEN `start` AND `end`";
        $qry = mysqli_query($this->con, $sql);
        if (mysqli_num_rows($qry) == 0){
            $ssql ="SELECT * FROM `subscriptions` WHERE `member_id` = '$member' ORDER BY `end` DESC ";
            $sqry = mysqli_query($this->con, $ssql);
            $rs = mysqli_fetch_assoc($sqry);
            return array(
                'status' => false,
                'date' => $rs['end']
            );
        }else{
            $rs = mysqli_fetch_assoc($qry);
            $bill = $rs['bill_id'];
            $bsql = "SELECT `paid_on` FROM `payment` WHERE `id` = '$bill' ";
            $bqry = mysqli_query($this->con, $bsql);
            $drs = mysqli_fetch_assoc($bqry);

            return array(
                'status' => true,
                'date' => $drs['paid_on']
            );
        }
    }

    public function registrationDate($id){
        $sql = "SELECT `created` FROM `members` WHERE  `id` = '$id'";
        $qry = mysqli_query($this->con, $sql);
        $rs = mysqli_fetch_assoc($qry);
        return $rs['created'];
    }

    public function packageName($id){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://ussd.ultramedhealth.com/api/v1/ussd/subscriptions/packages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $data = json_decode($response, true);

        if ($err) {

        }
        else {
            if ($data['success'] == true) {
                foreach ($data['packages'] as $key) {

                    if ($key['id'] == $id) {
                        return $key['name'];
                    }else{
                        return null;
                    }
                }
            }
        }
    }

}