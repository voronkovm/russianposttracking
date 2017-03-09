<?php

class RussianPostTracking
{
    public function __construct($login, $password, $lang = "RUS")
    {
        $this->login = $login;
        $this->password = $password;
        $this->lang = $lang;
        $this->client = new SoapClient("https://tracking.russianpost.ru/rtm34?wsdl", array('trace' => 1, 'soap_version' => SOAP_1_2));
    }
    
    public function getOperationHistory($barcode)
    {
        $response = $this->client->__doRequest(
            '<?xml version="1.0" encoding="UTF-8"?>
                <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:oper="http://russianpost.org/operationhistory" xmlns:data="http://russianpost.org/operationhistory/data" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soap:Header/>
                <soap:Body>
                   <oper:getOperationHistory>
                      <data:OperationHistoryRequest>
                         <data:Barcode>'.$barcode.'</data:Barcode>  
                         <data:MessageType>0</data:MessageType>
                         <data:Language>'.$this->lang.'</data:Language>
                      </data:OperationHistoryRequest>
                      <data:AuthorizationHeader soapenv:mustUnderstand="1">
                         <data:login>'.$this->login.'</data:login>
                         <data:password>'.$this->password.'</data:password>
                      </data:AuthorizationHeader>
                   </oper:getOperationHistory>
                </soap:Body>
             </soap:Envelope>',
             "https://tracking.russianpost.ru/rtm34",
             "getOperationHistory",
             SOAP_1_2
        );
        
        //echo $response;
        
        $xml = simplexml_load_string($response);
        $error =  $xml->children('S', true)->Body->Fault;
        if ($error) {
            $error_title = $error->Reason->Text;
            
            $error_text = false;
            $error = $error->Detail->children('ns3', true);
            $error_text = $error->OperationHistoryFaultReason ? $error->OperationHistoryFaultReason : $error_text;
            $error_text = $error->AuthorizationFaultReason ? $error->AuthorizationFaultReason : $error_text;
            $error_text = $error->LanguageFaultReason ? $error->LanguageFaultReason : $error_text;
            
            $error_text = $error_text ? $error_text : $response;
            $error_title = $error_title ? $error_title : "Unknown error";
            
            throw new RussianPostTrackingException($error_title.": ".$error_text);
        }
        
        $rows = $xml->children('S', true)->Body->children('ns7', true)->getOperationHistoryResponse->children('ns3', true)->OperationHistoryData->historyRecord;
        
        return $rows;
    }
    
    public function PostalOrderEventsForMail($barcode)
    {
        $response = $this->client->__doRequest(
            '<?xml version="1.0" encoding="UTF-8"?>
				<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:oper="http://russianpost.org/operationhistory" xmlns:data="http://russianpost.org/operationhistory/data" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:data1="http://www.russianpost.org/RTM/DataExchangeESPP/Data">
                <soap:Header/>
                <soap:Body>
                   <oper:PostalOrderEventsForMail>
                      <data:AuthorizationHeader soapenv:mustUnderstand="1">
                         <data:login>'.$this->login.'</data:login>
                         <data:password>'.$this->password.'</data:password>
                      </data:AuthorizationHeader>
					  <data1:PostalOrderEventsForMailInput Barcode="'.$barcode.'" Language="'.$this->lang.'"/>
                   </oper:PostalOrderEventsForMail>
                </soap:Body>
             </soap:Envelope>',
             "https://tracking.russianpost.ru/rtm34",
             "PostalOrderEventsForMail",
             SOAP_1_2
        );
        
        //echo $response;
        
        $xml = simplexml_load_string($response);
        
        $data = $xml->children('S', true)->Body->children('ns7', true)->PostalOrderEventsForMailResponse->children('ns6', true)->PostalOrderEventsForMaiOutput->children()->PostalOrderEvent;

        return $data;
    }
}
