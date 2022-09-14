<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Description of User Sign Up Email Controller
 *
 * @category webservice
 *
 * @package basic_appineers_master
 *
 * @subpackage controllers
 *
 * @module User Sign Up Email
 *
 * @class User_sign_up_email.php
 *
 * @path application\webservice\basic_appineers_master\controllers\User_sign_up_email.php
 *
 * @version 4.4
 *
 * @author Suresh Nakate
 *
 * @since 06.09.2021
 */
class Demo_api extends Cit_Controller
{
    /** @var array $output_params contains output parameters */
    public $output_params;

    /** @var array $single_keys contains single array */
    public $single_keys;

    /** @var array $multiple_keys contains multiple array */
    public $multiple_keys;

    /** @var array $block_result contains query returns result array*/
    public $block_result;

    /**
     * To initialize class objects/variables.
     */
    public function __construct()
    {
        parent::__construct();
        $this->output_params = array();
        $this->single_keys = array(
            "create_user",
            "delete_post_image",
            "get_user_details",
        );
        $this->multiple_keys = array(
            "format_email_v4",
            "custom_function",
            "email_verification_code",
        );
        $this->block_result = array();

        $this->load->library('wsresponse');
        $this->load->library('lib_log');
        $this->load->model('demo_api_model');
        $this->load->model("basic_appineers_master/users_model");

    }

    /**
     * This method is used to validate api input params.
     * 
     * @modified Suresh Nakate | 31.08.2021
     *
     * @param array $request_arr request input array.
     *
     * @return array $valid_res validation output response.
     */
    public function rules_demo_api($request_arr = array())
    {
         $valid_arr = array(
            "post_title" => array(
                array(
                    "rule" => "required",
                    "value" => true,
                    "message" => "post_title_required",
                ),
                array(
                    "rule" => "minlength",
                    "value" => FIRST_NAME_MIN_LENGTH,
                    "message" => "post_title_minlength",
                ),
                array(
                    "rule" => "maxlength",
                    "value" => FIRST_NAME_MAX_LENGTH,
                    "message" => "post_title_maxlength",
                ), 
                array(
                    "rule" => "regex",
                    "value" => "/^[a-zA-Z ]*$/",
                    "message" => "post_title_alphabets_with_spaces",
                ),

            ),
            "post_decreption" => array(
                array(
                    "rule" => "required",
                    "value" => true,
                    "message" => "post_decreption_required",
                ),
                array(
                    "rule" => "minlength",
                    "value" => LAST_NAME_MIN_LENGTH,
                    "message" => "post_decreption_minlength",
                ),
                array(
                    "rule" => "maxlength",
                    "value" => LAST_NAME_MAX_LENGTH,
                    "message" => "post_decreption_maxlength",
                ),
                array(
                    "rule" => "regex",
                    "value" => "/^[a-zA-Z ]*$/",
                    "message" => "post_decreption_alphabets_with_spaces",
                ),
            ),
            "posted_by" => array(
                array(
                    "rule" => "regex",
                    "value" => "/^[0-9a-zA-Z]+$/",
                    "message" => "posted_by_alpha_numeric_without_spaces",
                ),
                array(
                    "rule" => "minlength",
                    "value" => USER_NAME_MIN_LENGTH,
                    "message" => "posted_by_minlength",
                ),
                array(
                    "rule" => "maxlength",
                    "value" => USER_NAME_MAX_LENGTH,
                    "message" => "posted_by_maxlength",
                )
            )   
        );
       $this->wsresponse->setResponseStatus(UNPROCESSABLE_ENTITY);
        $valid_res = $this->wsresponse->validateInputParams($valid_arr, $request_arr, "demo_api");

        return $valid_res;
    }

    /**
     * This method is used to initiate api execution flow.
     * 
     * @modified Suresh Nakate | 31.08.2021
     *
     * @param array $request_arr request_arr array is used for api input.
     * @param bool $inner_api inner_api flag is used to idetify whether it is inner api request or general request.
     *
     * @return array $output_response returns output response of API.
     */
    public function start_demo_api($request_arr = array(), $inner_api = false)
    {   

        $method = $_SERVER['REQUEST_METHOD'];
        $output_response = array();
     
        switch ($method) {
            case 'GET':
                if (isset($request_arr['post_id'])) {
                    $output_response =  $this->getPostDetails($request_arr);
                } else {
                    $output_response =  $this->getPosts($request_arr);
                }

                return  $output_response;
                break;
            case 'POST':
                if (isset($request_arr['post_id'])) {
                    $output_response =  $this->updatePost($request_arr);
                } else {
                    $output_response =  $this->addPost($request_arr);
                }
               
                return  $output_response;
                break;

            case 'DELETE':
                $output_response = $this->delete_Post($request_arr);

                return  $output_response;
                break;
        }
         

    }

    public function addPost ($request_arr , $inner_api = false)
    {
        $this->block_result = array();

        try {
            $validation_res = $this->rules_demo_api($request_arr);
            
            if ($validation_res["success"] == FAILED_CODE) { //Validation Failed
                if ($inner_api === true) {
                    return $validation_res;
                } else {
                    $this->wsresponse->sendValidationResponse($validation_res);
                }
            }
            $output_response = array();
            $input_params = $validation_res['input_params'];
            $output_array = $func_array = array();
            
                $input_params = $this->create_user($input_params);

                if ($input_params["create_user"]["success"]) {

                    $output_response = $this->users_finish_success($input_params);

                    return $output_response;
                } else {
                    $output_response = $this->users_finish_success_1($input_params);

                    return $output_response;
                }
            
        } catch (Exception $e) {
            $this->general->apiLogger($input_params, $e);
            $success = 0;
            $message = $e->getMessage();
        }

        return $output_response;
    }

    public function getPosts($input_params)
    {
        $this->block_result = array();
        try {
            
            $this->block_result = $this->users_model->posts_list();
            
            if (!$this->block_result["success"]) {
                throw new Exception("No records found.");
            }
            $result_arr = $this->block_result["data"];

            if (is_array($result_arr) && count($result_arr) > 0) {
                $i = 0;

                $this->block_result["data"] = $result_arr;
            }
            
            $this->block_result['data'] = array_map(function (array $arr) {
                $image_arr = array();
                $image_arr["image_name"] = $arr["profile_image"];
                $image_arr["ext"] = implode(",", $this->config->item("IMAGE_EXTENSION_ARR"));

                $image_arr["color"] = "FFFFFF";
                $image_arr["no_img"] = false;
                $image_arr["path"]=  $this->config->item("AWS_FOLDER_NAME") . "/user_profile/tushark";
                $data_1 = $this->general->get_image_aws($image_arr);
                $arr['post_images'] = $data_1;

                 //get user images
                if(false== empty($arr["forum_id"])){
                    $arrImage = $this->forum_model->forum_media($arr);
                }
                
                $arrImage = (false == empty($arrImage["data"])) ? $arrImage["data"] : array();
                $arr["forum_media"] =  $arrImage ; 
                

                return $arr;
            }, $this->block_result['data']);

            if (!$this->block_result["success"]) {
                throw new Exception("Data not fetched successfully.");
            }
        } catch (Exception $e) {
            $this->general->apiLogger($input_params, $e);
            $success = 0;
            $this->block_result["data"] = array();
        }
        $input_params["PostsData"] = $this->block_result["data"];

        return $input_params;
    }

    public function getPostDetails($input_params = array())
    {
        $this->block_result = array();
        try {
            $post_id = isset($input_params["post_id"]) ? $input_params["post_id"] : "";
            $this->block_result = $this->users_model->get_posts_details($post_id);
            
            if (!$this->block_result["success"])
            {
                throw new Exception("No records found.");
            }
            $result_arr = $this->block_result["data"];
            
            if (is_array($result_arr) && count($result_arr) > 0)
            {
                $i = 0;
                foreach ($result_arr as $data_key => $data_arr)
                {

                    $data = $data_arr["post_Image"];
                    $image_arr = array();
                    $image_arr["image_name"] = $data;
                    $image_arr["ext"] = implode(",", $this->config->item("IMAGE_EXTENSION_ARR"));
                    $image_arr["color"] = "FFFFFF";
                    $image_arr["no_img"] = FALSE;
                    $aws_folder_name = $this->config->item("AWS_FOLDER_NAME");
                    $image_arr["path"] =$aws_folder_name."/user_profile/tushark/".$post_id;
                    
                    $data = $this->general->get_image_aws($image_arr);
                    
   
                }
                $result_arr["post_images"] = (false == empty($data)) ? $data:"";
                $this->block_result["data"] = $result_arr;
            }

        } catch (Exception $e) {
            $this->general->apiLogger($input_params, $e);
            $success = 0;
            $this->block_result["data"] = array();
        }
        $input_params["get_user_details"] = $this->block_result["data"];

        // print_r($input_params); exit;

        $input_params = $this->wsresponse->assignSingleRecord($input_params, $this->block_result["data"]);

        return $input_params;
    }

    public function updatePost($input_params)
    {
        $this->block_result = array();
        try {

            $params_arr = $where_arr = array();
            if (isset($input_params["post_id"])) {
                $where_arr["post_id"] = $input_params["post_id"];
            }
            if (isset($input_params["post_title"])) {
                $params_arr["post_title"] = $input_params["post_title"];
            }
            if (isset($input_params["post_decreption"])) {
                $params_arr["post_decreption"] = $input_params["post_decreption"];
            }
            if (isset($input_params["posted_by"])) {
                $params_arr["posted_by"] = $input_params["posted_by"];
            }
            if (isset($input_params["post_type"])) {
                $params_arr["post_type"] = $input_params["post_type"];
            }
            
           if (isset($images_arr["post_image"]["name"])) {
                $params_arr["post_image"] = $images_arr["post_image"]["name"];
            }

            $params_arr["updated_at"] = "NOW()";
            
            $this->block_result = $this->users_model->update_posts_details($params_arr, $where_arr);
        } catch (Exception $e) {
            $this->general->apiLogger($input_params, $e);
            $success = 0;
            $this->block_result["data"] = array();
        }
        $input_params["update_posts"] = $this->block_result["data"];
        $input_params = $this->wsresponse->assignSingleRecord($input_params, $this->block_result["data"]);

        return $input_params;
    }

    
        public function delete_post_image($input_params = array())
        {
            $this->block_result = array();
            try {
    
                $post_id = isset($input_params["post_id"]) ? $input_params["post_id"] : "";
                
                $this->block_result = $this->users_model->get_post_image($post_id);
               
                if (!$this->block_result["success"]) {
                    throw new Exception("No records found.");
                }
                $result_arr = $this->block_result["data"];
                
                if (is_array($result_arr) && count($result_arr) > 0)
                {
                    $aws_folder_name = $this->config->item("AWS_FOLDER_NAME");
                    $folder_name = $aws_folder_name . "/" . USER_PROFILE . "/tushark/" . $post_id;
                    
                    //$folder_name= "post_image_names/".$input_params["post_id"]."/";
                    $insert_arr = array();
                    $temp_var   = 0;
                    foreach($result_arr as $key=>$value)
                    {
                        
                        //$new_file_name=$value['post_images'];
                        if($value['post_images'] != "") {
                            $new_file_name=$value['post_images'];
                        } else {
                            $new_file_name=$value['post_video'];
                        }
                        
                        if(false == empty($new_file_name))
                        {  
                            $file_name = $new_file_name;
                            $res = $this->general->deleteAWSFileData($folder_name,$file_name);
                        }                      
                    }
    
                    $result_arr = $this->users_model->delete_images($post_id);
    
                    $this->block_result["data"] = $result_arr;
                }
            } catch (Exception $e) {
                $success = 0;
                $this->block_result["data"] = array();
                $this->general->apiLogger($input_params, $e);
            }
            $input_params["delete_post_image"] = $this->block_result["data"];
            $input_params = $this->wsresponse->assignSingleRecord($input_params, $this->block_result["data"]);
    
            return $input_params;
        }
        
    
    public function delete_Post ($input_params = array())
    {
        $this->block_result = array();

        try {
            
                $output_response = array();
                $output_array = $func_array = array();
            
                $input_params = $this->delete_post_image($input_params);
                
                if ($input_params["delete_post_image"]["success"]) {

                    $output_response = $this->users_finish_success_delete($input_params);

                    return $output_response;
                } else {
                    $output_response = $this->users_finish_success_1($input_params);

                    return $output_response;
                }
            
        } catch (Exception $e) {
            $this->general->apiLogger($input_params, $e);
            $success = 0;
            $message = $e->getMessage();
        }

        return $output_response;
    }
    /**
     * Used to process query block & create_user.
     * 
     * @modified Suresh Nakate | 31.08.2021
     * 
     * @param array $input_params array to process loop flow.
     *
     * @return array $input_params returns modified input_params array.
     */
    public function create_user($input_params = array())
    {
        $this->block_result = array();
        try {
            $params_arr = array();
            $images_arr = array();
            if (isset($_FILES["post_image"]["name"]) && isset($_FILES["post_image"]["tmp_name"])) {
                for ($i=0; $i <count($_FILES["post_image"]["name"]) ; $i++) { 
                    $sent_file [] = $_FILES["post_image"]["name"][$i];
                }
            } else {
                $sent_file = "";
            }
            if (!empty($sent_file)) {
                for ($j=0; $j <count($sent_file) ; $j++) { 
                
                    list($file_name, $ext) = $this->general->get_file_attributes($sent_file[$j]);
                    $images_arr["post_image"]["ext"][$j] = implode(',', $this->config->item('IMAGE_EXTENSION_ARR'));
                    $images_arr["post_image"]["size"][$j] = "2024000";
                    if ($this->general->validateFileFormat($images_arr["post_image"]["ext"][$j], $_FILES["post_image"]["name"][$j])) {
                        if ($this->general->validateFileSize($images_arr["post_image"]["size"][$j], $_FILES["post_image"]["size"][$j])) {
                             $images_arr["post_image"]["name"][$j] = $file_name;
                        }
                    }
                }
            }
            //print_r($images_arr);die;
            if (!empty($input_params["post_title"])) {
                $params_arr["post_title"] = $input_params["post_title"];
            }
            if (!empty($input_params["post_decreption"])) {
                $params_arr["post_decreption"] = $input_params["post_decreption"];
            }
            if (!empty($input_params["posted_by"])) {
                $params_arr["posted_by"] = $input_params["posted_by"];
            }
            if (!empty($input_params["post_type"])) {
                $params_arr["post_type"] = $input_params["post_type"];
            }
            
           
            
            $params_arr["created_at"] = "NOW()";
            
            $this->block_result = $this->users_model->create_user_demo($params_arr);

            if (!$this->block_result["success"]) {
                throw new Exception("Insertion failed.");
            }
            $data_arr = $this->block_result["data"]['0'];
            
            if (!empty($images_arr)) {
                for ($k=0; $k < count($images_arr["post_image"]["name"]); $k++) {     
                    
                    $aws_folder_name = $this->config->item("AWS_FOLDER_NAME");
                    $folder_name = $aws_folder_name . "/" . USER_PROFILE . "/tushark/" . $data_arr['insert_id'];
                    
                    $file_path = $folder_name;
                    
                    $file_name = $images_arr["post_image"]["name"][$k];
                    
                    $file_tmp_path = $_FILES["post_image"]["tmp_name"][$k];
                    $file_tmp_size = $_FILES["post_image"]["size"][$k];
                    $valid_extensions = $images_arr["post_image"]["ext"][$k];
                    $valid_max_size = $images_arr["post_image"]["size"][$k];
                    $upload_arr = $this->general->file_upload($file_path, $file_tmp_path, $file_name, $valid_extensions, $file_tmp_size, $valid_max_size);

                    if ($upload_arr[0] == "") {
                        throw new Exception("Uploading file(s) is failed.");
                    }
                    if (!empty($images_arr["post_image"]["name"])) {
                        $params_arr["post_image"] =  $images_arr["post_image"]["name"][$k];
                    }
                    if (!empty($data_arr['insert_id'])) {

                        $params_arr["u_user_id"] = $data_arr['insert_id'];
                    }
                    $arrupdate = $this->users_model->update_image_demo($params_arr);
                }
             
            }
        } catch (Exception $e) {
            $this->general->apiLogger($input_params, $e);
            $success = 0;
            $this->block_result["data"] = array();
        }
        $input_params["create_user"] = $this->block_result;
        $input_params = $this->wsresponse->assignSingleRecord($input_params, $this->block_result["data"]);

        return $input_params;
    }



    /**
     * Used to process finish flow.
     *
     * @param array $input_params  array to process loop flow.
     *
     * @return array $responce_arr returns responce array of api.
     */
    public function users_finish_success($input_params = array())
    {
        $setting_fields = array(
            "success" => SUCCESS_CODE,
            "message" => "users_finish_success",
        );
        $output_fields = array();

        $output_array["settings"] = $setting_fields;
        $output_array["settings"]["fields"] = $output_fields;
        $output_array["data"] = $input_params;

        $func_array["function"]["name"] = "demo_api";
        $func_array["function"]["single_keys"] = $this->single_keys;
        $func_array["function"]["multiple_keys"] = $this->multiple_keys;

        $this->wsresponse->setResponseStatus(CREATED);

        $responce_arr = $this->wsresponse->outputResponse($output_array, $func_array);

        return $responce_arr;
    }

    /**
     * Used to process finish flow.
     *
     * @param array $input_params  array to process loop flow.
     *
     * @return array $responce_arr returns responce array of api.
     */
    public function users_finish_success_delete($input_params = array())
    {
        $setting_fields = array(
            "success" => SUCCESS_CODE,
            "message" => "users_finish_success_delete",
        );
        $output_fields = array();

        $output_array["settings"] = $setting_fields;
        $output_array["settings"]["fields"] = $output_fields;
        $output_array["data"] = $input_params;

        $func_array["function"]["name"] = "demo_api";
        $func_array["function"]["single_keys"] = $this->single_keys;
        $func_array["function"]["multiple_keys"] = $this->multiple_keys;

        $this->wsresponse->setResponseStatus(OK);

        $responce_arr = $this->wsresponse->outputResponse($output_array, $func_array);

        return $responce_arr;
    }
    /**
     * Used to process finish API failure flow.
     *
     * @param array $input_params input_params array to process loop flow.
     *
     * @return array $responce_arr returns responce array of api.
     */
    public function users_finish_success_1($input_params = array())
    {
        $setting_fields = array(
            "success" => FAILED_CODE,
            "message" => "users_finish_success_1",
        );
        $output_fields = array();

        $output_array["settings"] = $setting_fields;
        $output_array["settings"]["fields"] = $output_fields;
        $output_array["data"] = $input_params;

        $func_array["function"]["name"] = "demo_api";
        $func_array["function"]["single_keys"] = $this->single_keys;
        $func_array["function"]["multiple_keys"] = $this->multiple_keys;

        $this->wsresponse->setResponseStatus(INTERNAL_SERVER_ERROR);

        $responce_arr = $this->wsresponse->outputResponse($output_array, $func_array);

        return $responce_arr;
    }

    /**
     * Used to process finish API failure flow.
     *
     * @param array $input_params input_params array to process loop flow.
     *
     * @return array $responce_arr returns responce array of api.
     */
    public function finish_success_1($input_params = array())
    {
        $setting_fields = array(
            "success" => FAILED_CODE,
            "message" => "finish_success_1",
        );
        $output_fields = array();

        $output_array["settings"] = $setting_fields;
        $output_array["settings"]["fields"] = $output_fields;
        $output_array["data"] = $input_params;

        $func_array["function"]["name"] = "demo_api";
        $func_array["function"]["single_keys"] = $this->single_keys;
        $func_array["function"]["multiple_keys"] = $this->multiple_keys;

        $this->wsresponse->setResponseStatus(CONFLICT);

        $responce_arr = $this->wsresponse->outputResponse($output_array, $func_array);

        return $responce_arr;
    }
}
