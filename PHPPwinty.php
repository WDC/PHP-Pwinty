<?php
include_once('config.php');
/**
 * A PHP implementation of the Pwinty HTTP API - http://www.pwinty.com/api.html
 * Developed by Brad Pineau for Picisto.com. Released to public under Creative Commons.
 * 
 * Configuration values are located in the config.php file.
 * 
 * @author Brad Pineau
 * @author David M. Lemcoe Jr.
 * @author Picisto.com
 * @version 1.0
 * @access public
 * @see http://www.bradpineau.com/PHPPwinty/
 * @see https://github.com/WDC/PHP-Pwinty/
 */
class PHPPwinty {

    var $api_url = "";
    var $last_error = "";

    /**
     * The class constructor
     *
     * @access private
     */
    private function PHPPwinty() {
        if (PWINTY_LIVE) {
            $this->api_url = "https://api.pwinty.com";
        } else {
            $this->api_url = "https://sandbox.pwinty.com";
        }
    }

    /**
     * Sends a HTTP request to the Pwinty API. This should not be called directly.
     *
     * @param string $call The API call.
     * @return array The response returned from the API call.
     * @access private
     */
    private function apiCall($call, $data, $method) {
        /*
          internal function, you shouldn't call directly
         */
        $url = $this->api_url . $call;
        if (($method != "POST")) {
            $url .= "?" . http_build_query($data);
        }

        $headers = array();
        $headers[] = 'X-Pwinty-MerchantId: ' . PWINTY_MERCHANTID;
        $headers[] = 'X-Pwinty-REST-API-Key: ' . PWINTY_APIKEY;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ($method == "GET") {
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } elseif ($method == "PUT") {
            curl_setopt($ch, CURLOPT_PUT, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($method == "DELETE") {
            curl_setopt($ch, CURLOPT_PUT, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, "PHPPwinty v1");

        $result_text = curl_exec($ch);
        $curl_request_info = curl_getinfo($ch);
        curl_close($ch);

        if ($curl_request_info["http_code"] == 401) {
            $this->last_error = "Authorization unsuccessful. Check your Merchant ID and API key.";
            return array();
        }

        $data = json_decode($result_text, true);
        return $data;
    }

    /**
     * Creates a new order
     *
     * @param string $recipientName Who the order should be addressed to
     * @param string $address1 1st line of recipient address
     * @param string $address2optional 2nd line of recipient address
     * @param string $addressTownOrCity Town or City in the address
     * @param string $stateOrCounty State or County in the address
     * @param string $postalOrZipCode Postal code or Zip code of recipient
     * @param string $country Country of recipient (We support pretty much every country in the world)
     * @param string $textOnReverse optional text to be printed on the back of each photo in the order (max 27 characters, alpha numeric only)
     * @return string The newly created order id
     * @access public
     */
    public function createOrder($recipientName, $address1, $address2, $addressTownOrCity, $stateOrCounty, $postalOrZipCode, $country, $textOnReverse) {
        $data = array();
        $data["recipientName"] = $recipientName;
        $data["address1"] = $address1;
        $data["address2"] = $address2;
        $data["addressTownOrCity"] = $addressTownOrCity;
        $data["stateOrCounty"] = $stateOrCounty;
        $data["postalOrZipCode"] = $postalOrZipCode;
        $data["country"] = $country;
        $data["textOnReverse"] = $textOnReverse;

        $data = $this->apiCall("/Orders", $data, "POST");

        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data["id"];
            }
        } else {
            return 0;
        }
    }

    /**
     * Retrieves information about all your orders, or a specific order
     *
     * @param string $id the id of a specific order to retrieve information on
     * @return array The order details
     * @access public
     */
    public function getOrder($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Orders", $data, "GET");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Updates an existing order
     *
     * @param string $id the id of the order to update
     * @param string $recipientName Who the order should be addressed to
     * @param string $address1 1st line of recipient address
     * @param string $address2optional 2nd line of recipient address
     * @param string $addressTownOrCity Town or City in the address
     * @param string $stateOrCounty State or County in the address
     * @param string $postalOrZipCode Postal code or Zip code of recipient
     * @param string $country Country of recipient (We support pretty much every country in the world)
     * @param string $textOnReverse optional text to be printed on the back of each photo in the order (max 27 characters, alpha numeric only)
     * @return array The order details
     * @access public
     */
    public function updateOrder($id, $recipientName, $address1, $address2, $addressTownOrCity, $stateOrCounty, $postalOrZipCode, $country, $textOnReverse) {
        $data = array();
        $data["id"] = $id;
        $data["recipientName"] = $recipientName;
        $data["address1"] = $address1;
        $data["address2"] = $address2;
        $data["addressTownOrCity"] = $addressTownOrCity;
        $data["stateOrCounty"] = $stateOrCounty;
        $data["postalOrZipCode"] = $postalOrZipCode;
        $data["country"] = $country;
        $data["textOnReverse"] = $textOnReverse;

        $data = $this->apiCall("/Orders", $data, "PUT");

        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return 1;
            }
        } else {
            return 0;
        }
    }

    /**
     * Update the status of an order (to submit or cancel it)
     *
     * @param string $id Order id
     * @param string $status Status to which the order should be updated. Valid values are "Cancelled" or "Submitted"
     * @return array The order details
     * @access public
     */
    public function updateOrderStatus($id, $status) {
        $data = array();
        $data["id"] = $id;
        $data["status"] = $status;

        $data = $this->apiCall("/Orders/Status", $data, "POST");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Gets information on whether the order is ready for submission, and any errors or warnings associated with the order
     *
     * @param string $id Order id
     * @return array The order submission status
     * @access public
     */
    public function getOrderSubmissionStatus($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Orders/SubmissionStatus", $data, "GET");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Add a photo to an order - PWINTY does support file uploading, but this class only allows photos via URL
     *
     * @param string $orderId the id of the order the photo is being added to
     * @param string $type the type/size of photo (available photo types)
     * @param string $url the url from which we can download it
     * @param string $copies the number of copies of the photo to include in the order
     * @param string $sizing how the image should be resized when printing (resizing options)
     * @return array The order submission status
     * @access public
     */
    public function addPhoto($orderId, $type, $url, $copies, $sizing) {
        $data = array();
        $data["orderId"] = $orderId;
        $data["type"] = $type;
        $data["url"] = $url;
        $data["copies"] = $copies;
        $data["sizing"] = $sizing;

        $data = $this->apiCall("/Photos", $data, "POST");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Retrieves information about a specific photo
     *
     * @param string $id the id of the photo
     * @return array The photo details
     * @access public
     */
    public function getPhoto($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Photos", $data, "GET");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Removes a specific photo from an order
     *
     * @param string $id the id of the photo
     * @return string The status of the delete
     * @access public
     */
    public function deletePhoto($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Photos", $data, "DELETE");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Add a document to an order
     *
     * @param string $orderId the id of the order the document is being added to
     * @param string $file the full path filefame being uploaded, must be a pdf or a docx file
     * @return array The document details
     * @access public
     */
    public function addDocument($orderId, $file) {
        $path_parts = pathinfo($file);

        $data = array();
        $data["orderId"] = $orderId;
        $data["fileName"] = $path_parts['basename'];
        $data["file"] = "@" . $file;

        $data = $this->apiCall("/Documents", $data, "POST");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Retrieves information about a specific document
     *
     * @param string $id the id of the document
     * @return array The document details
     * @access public
     */
    public function getDocument($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Documents", $data, "GET");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Removes a specific document from an order
     *
     * @param string $id the id of the document
     * @return string The status of the delete
     * @access public
     */
    public function deleteDocument($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Documents", $data, "DELETE");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Add a sticker to an order
     *
     * @param string $orderId the id of the order the sticker is being added to
     * @param string $file the full path filefame of the sticker being uploaded, must be an image file
     * @return array The document details
     * @access public
     */
    public function addSticker($orderId, $file) {
        $path_parts = pathinfo($file);

        $data = array();
        $data["orderId"] = $orderId;
        $data["fileName"] = $path_parts['basename'];
        $data["file"] = "@" . $file;

        $data = $this->apiCall("/Stickers", $data, "POST");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Retrieves information about a specific sticker
     *
     * @param string $id the id of the sticker
     * @return array The sticker details
     * @access public
     */
    public function getSticker($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Stickers", $data, "GET");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }

    /**
     * Removes a specific sticker from an order
     *
     * @param string $id the id of the sticker
     * @return string The status of the delete
     * @access public
     */
    public function deleteSticker($id) {
        $data = array();
        $data["id"] = $id;

        $data = $this->apiCall("/Stickers", $data, "DELETE");
        if (is_array($data)) {
            if (isset($data["error"])) {
                $this->last_error = $data["error"];
                return 0;
            } else {
                return $data;
            }
        } else {
            return 0;
        }
    }
}
?>

