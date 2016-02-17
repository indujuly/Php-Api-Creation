<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
session_start();
class V1 extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper(array('url','language'));
		$this->load->model('Welcome_model');
		$this->load->library('session');
		$this->load->library('form_validation');	
       // $data = json_decode(file_get_contents('php://input'), true);  // for server json methed
       // print_r($data);
    }
	public function getPrice()
	{
        $data = json_decode(file_get_contents('php://input'), true);  // for server json methed
       // print_r($data);
       $itemweight=$data['itemweight'];
        $transactiontype=$data['transactiontype'];
        $address=$data['address'];
        $currencytype=$data['currencytype']; 
        
        /* require the user as the parameter */
          
            if(!$currencytype) {  $currencytype="SGD"; } 
            // for transit amount    
           // echo "SELECT $currencytype FROM transit_amount WHERE transactiontype='$transactiontype'";        
            $sql_tamount=mysql_fetch_array(mysql_query("SELECT $currencytype FROM transit_amount WHERE transactiontype='$transactiontype'"));
             $tt=mysql_affected_rows();
             // for weight_amount
            //echo "SELECT $currencytype FROM weight_amount WHERE weight='$itemweight'";
            $sql_wamount=mysql_fetch_array(mysql_query("SELECT $currencytype FROM weight_amount WHERE weight='$itemweight'"));
            $wt=mysql_affected_rows();
            if($tt && $wt)
            {
	            $tamount = $sql_tamount[$currencytype]; //echo "<br>";
                $wamount = $sql_wamount[$currencytype];
                $paymenttotal=$tamount+$wamount;
              
               
               $e->payment_total  = $paymenttotal;
               $e->currency_type = $currencytype;
               $e->itemweight  = $itemweight;
               $e->transactiontype  = $transactiontype;
               $e->address  = $address;
               $e->status = "Success";
               $d = array("data" => $e);
	            
            } 
            elseif(!$wt)
            {
                $e->message = "This $itemweight is Wrong. Let's try once again!";
                $e->status = "Failure";
                $d = array("data" => $e);
                // $d = array("message" => "This Weight is Wrong. Let's try once again!","status"=>"Failure");
            }
            elseif(!$tt)
            {
                $e->message = "This Transit Type is Wrong. Let's try once again!";
                $e->status = "Failure";
                $d = array("data" => $e);
                // $d = array("message" => "This Transit Type is Wrong. Let's try once again!","status"=>"Failure");
            }
 
         
         /* disconnect from the db */
	    @mysql_close($link);
        header('Content-type: application/json');          
	     echo json_encode($d); 
        
     }   
	

	public function orderNow()
    {
        $data = json_decode(file_get_contents('php://input'), true);  // for server json methed
       //print_r($data);
       $itemweight=$data['itemweight'];
        $transactiontype=$data['transactiontype'];
        $address=$data['address'];
        $currencytype=$data['currencytype']; 
    


      /* require the user as the parameter */
            
            if(!$currencytype) {   $currencytype="SGD"; } 
            // for transit amount    
            //echo "SELECT days,$currencytype FROM transit_amount WHERE transactiontype='$transactiontype'";        
            $sql_tamount=mysql_fetch_array(mysql_query("SELECT days,$currencytype FROM transit_amount WHERE transactiontype='$transactiontype'"));
             $tt=mysql_affected_rows();
             // for weight_amount
            //echo "SELECT $currencytype FROM weight_amount WHERE weight='$itemweight'";
            $sql_wamount=mysql_fetch_array(mysql_query("SELECT $currencytype FROM weight_amount WHERE weight='$itemweight'"));
            $wt=mysql_affected_rows();
            if($tt && $wt)
            {
	           $tamount = $sql_tamount[$currencytype]; //echo "<br>";
               $wamount = $sql_wamount[$currencytype];

                $days = $sql_tamount['days'];
                $fromdate=date('Y-m-d');
                define('SECONDS_PER_DAY', 86400);
                $todate = date('Y-m-d', time() + $days * SECONDS_PER_DAY);

            // Insert Query
                  $sql_parcel="INSERT into tracking (fromdate,todate,address,currencytype,weight,wamount,transactiontype,tamount) values('$fromdate','$todate','$address','$currencytype', '$itemweight', '$wamount','$transactiontype','$tamount')";
                
               $dd= mysql_query($sql_parcel)or die('Insert query:  '.mysql_error());
                 $lastid=mysql_insert_id(); 
                 $tra=1000+$lastid;
                 $tracking_id="IDR".$tra; 
                $sql_update=mysql_query("UPDATE tracking SET tracking_id='$tracking_id' WHERE id='$lastid'");
               
                $select=mysql_query("SELECT * FROM tracking WHERE id='$lastid'");           
	            /* create one master array of the records */
	           	  if(mysql_num_rows($select)) 
                  {
        	           $data = mysql_fetch_array($select);
                       $paymenttotal=$data['wamount']+$data['tamount'];                   
                       $tracking_id= $data['tracking_id'];

                       if($transactiontype=="1") { $servicetype="Air Now"; } elseif($transactiontype=="2") { $servicetype="Air Premium"; }
                      
                       
                       $e->currency_type = $data['currencytype'];
                       $e->transit_amount  = $tamount;
                       $e->weight_amount  = $wamount;
                       $e->payment_total  = $paymenttotal;
                       $e->service_type  = $servicetype; 
                       $e->product_weight  = $data['weight']; 
                       $e->tracking_id = $tracking_id;
                       $e->status = "Success";
                       $d = array("data" => $e);
	            }
            } 
            elseif(!$wt)
            {
                $e->message = "This Weight is Wrong. Let's try once again!";
                $e->status = "Failure";
                $d = array("data" => $e);
                // $d = array("message" => "This Weight is Wrong. Let's try once again!","status"=>"Failure");
            }
            elseif(!$tt)
            {
                $e->message = "This Transit Type is Wrong. Let's try once again!";
                $e->status = "Failure";
                $d = array("data" => $e);
                //$d = array("message" => "This Transit Type is Wrong. Let's try once again!","status"=>"Failure");
            }
         
         /* disconnect from the db */
	    @mysql_close($link);
        header('Content-type: application/json');          
	     echo json_encode($d); 
     }

    public function userDetails()
        {           
             $data = json_decode(file_get_contents('php://input'), true);  // for server json methed
            $collectionOption=$data['collectionOption'];
            $itemDesc=$data['itemDesc'];
            $boxing=$data['boxing'];
            $pickAddress=$data['pickAddress'];
            $shopName=$data['shopName'];
            $dateDelivery=$data['dateDelivery'];
            $trackingId=$data['trackingId'];
            $contactName=$data['contactName'];
            $contactEmail=$data['contactEmail'];
            $contactMobile=$data['contactMobile']; 

            if($trackingId)
            {      
               $sql_mobile=mysql_query("SELECT * from users WHERE mobileno='$contactMobile'"); 
               $row=mysql_affected_rows();   
               if(!$row) 
               {   
               $sql_user=mysql_query("INSERT into users (name,email,mobileno) values('$contact_name','$contact_email','$contact_mobile')");
               $lastid1=mysql_insert_id();
               $message = "A user account was created";
               // echo "INSERT into tracking_master (uid,pid) values('$lastid1','$tracking_id' " ; exit;
               $sql_master=mysql_query("INSERT into tracking_master (uid,pid) values('$lastid1','$trackingId') "); 
              } else 
              {
               $message = "Existing User ";
               $fetch=mysql_fetch_array($sql_mobile);
               $lastid1=$fetch['user_id']; 
              }
               $sqlparcel=mysql_query("UPDATE tracking SET collection_option='$collection_option',item_desc='$item_desc', boxing='$boxing',pick_address='$pick_address',shop_name='$shop_name',date_delivery='$date_delivery',uid='$lastid1' WHERE tracking_id='$trackingId' ");
                       //
                       $e->message = $message;
                       $e->userId = $lastid1;
                       $e->status = "Success";
                       $d = array("data" => $e);
	                                       
            }            
            else
            {
                $e->message = "Please send Tracking id!";
                $e->status = "Failure";
                $d = array("data" => $e);
                //$d = array( "message" => "Please send Tracking id!","status" => "Failure");
            }
         
        /* disconnect from the db */
	    @mysql_close($link);
        header('Content-type: application/json');          
	     echo json_encode($d);
        }

      // paymentMethodApi
    function paymentMethod()
    {
            $data = json_decode(file_get_contents('php://input'), true);  // for server json methed
            $paymenttype=$data['paymenttype'];
            $pamount=$data['pamount'];
            $trackingId=$data['trackingId'];
            $paymenttransationId=$data['paymenttransationId'];
            $userid=$data['userid']; 
            if($paymenttype=="paypal")
            {
            $sql_payment=mysql_query("INSERT into payment (paymenttype,pamount,trackingId,userid) values('$paymenttype','$pamount','$trackingId','$userid') ");
            $paymentId=mysql_insert_id();
            
                       $e->paymentId = $paymentId;
                       $e->userId = $userid;
                       $e->trackingId = $trackingId;
                       $e->status = "Success";
                       $d = array("data" => $e);                               
            }            
            else
            {
                $e->message = "This is not paypal ID";
                $e->status = "Failure";
                $d = array("data" => $e);
                //$d = array( "message" => "Payment Transation Id Missing","status" => "Failure");
            }
            /* disconnect from the db */
	        @mysql_close($link);
            header('Content-type: application/json');          
	         echo json_encode($d); 
        }  
        function getCurrentStatusbyId()
        {
            $data = json_decode(file_get_contents('php://input'), true);  // for server json methed
             $trackingId=$data['trackingId'];
            if($trackingId)
            {
              // echo "SELECT * FROM tracking WHERE tracking_id='$trackingId'";
              $select=mysql_query("SELECT * FROM tracking WHERE tracking_id='$trackingId'");           
	            /* create one master array of the records */
	           	  if(mysql_num_rows($select)) 
                  {
        	           $data = mysql_fetch_array($select);                       

                       $e->fromDate  = $data['fromdate']; 
                       $e->toDate  = $data['todate']; 
                       $e->trackingId = $trackingId;
                       $e->product_status = $data['status']; 
                       $e->status = "Success";
                       $d = array("data" => $e);
	            }
                else
                {
                $e->message = "TrackingId Id is not valid";
                $e->status = "Failure";
                $d = array("data" => $e);
                // $d = array( "message" => "TrackingId Id is not valid","status" => "Failure");
                }                              
            }            
            else
            {
                $e->message = "TrackingId Id is Missing";
                $e->status = "Failure";
                $d = array("data" => $e);
                // $d = array( "message" => "TrackingId Id is Missing","status" => "Failure");
            }
            /* disconnect from the db */
	        @mysql_close($link);
            header('Content-type: application/json');          
	         echo json_encode($d); 
        }


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php currencytype
