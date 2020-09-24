<?php
/**
 * amadeus-ws-client
 *
 * Copyright 2015 Amadeus Benelux NV
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package Amadeus
 * @license https://opensource.org/licenses/Apache-2.0 Apache 2.0
 */

namespace Amadeus\Client\ResponseHandler\Air;

use Amadeus\Client\ResponseHandler\StandardResponseHandler;
use Amadeus\Client\Result;
use Amadeus\Client\Session\Handler\SendResult;

/**
 * HandlerSellFromRecommendation
 *
 * @package Amadeus\Client\ResponseHandler\Air
 * @author Dieter Devlieghere <dermikagh@gmail.com>
 */
class HandlerSellFromRecommendation extends StandardResponseHandler
{
    /**
     * @param SendResult $response
     * @return Result
     */
    public function analyze(SendResult $response)
    {
        $analyzeResponse = new Result($response);

        $errMsgMap = [
            "288" => "UNABLE TO SATISFY, NEED CONFIRMED FLIGHT STATUS",
            "390" => "UNABLE TO REFORMAT"
        ];

        $domXpath = $this->makeDomXpath($response->responseXml);

        $codeNode = $domXpath->query("//m:errorSegment/m:errorDetails/m:errorCode")->item(0);
        if ($codeNode instanceof \DOMNode) {
            $analyzeResponse->status = Result::STATUS_ERROR;

            $categoryNode = $domXpath->query("//m:errorSegment/m:errorDetails/m:errorCategory")->item(0);
            if ($categoryNode instanceof \DOMNode) {
                $analyzeResponse->status = $this->makeStatusFromErrorQualifier($categoryNode->nodeValue);
            }

            $message = (array_key_exists($codeNode->nodeValue, $errMsgMap)) ?
                $errMsgMap[$codeNode->nodeValue] : 'UNKNOWN ERROR';

            $analyzeResponse->messages [] = new Result\NotOk($codeNode->nodeValue, $message);
        }

        return $analyzeResponse;
    }
}
