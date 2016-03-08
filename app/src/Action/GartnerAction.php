<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace USF\IdM\AuthTransfer\Gartner\Action;

/**
 * Description of GartnerAction
 *
 * @author James Jones <james@mail.usf.edu>
 */
class GartnerAction extends \USF\IdM\AuthTransfer\BasicAuthServiceAction {
    /**
     * Handles authentication for Gartner using CAS and Webtokens
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return type
     */
    public function dispatch(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        if($this->settings['gartner']['enabled'] ?? TRUE) {
            //Grab the Resource ID and/or the Document Code from the GET string
            $resId = $args["resId"] ?? '';
            $docCode = $args["docCode"] ?? '';

            //Netid will be sent to Gartner as 'uid'
            $username = $request->getHeaderLine('AUTH_PRINCIPAL');

            //eduPersonPrimaryAffiliation will be used for authorization and sent to Gartner as 'title'
            $eppa = $request->getHeaderLine('AUTH_ATTR_EDUPERSONPRIMARYAFFILIATION');

            $allowedEppaList = $this->settings['gartner']['allowedEppaList'] ?? [];
            if(!\in_array($eppa, $allowedEppaList)) {
                $this->logger->error("Gartner access denied for ePPA|${eppa}|${username}");            
                // Redirect to an error page if the EPPA isn't allowed
                return $this->view->render($response, 'error.html', ['allowedEppaList' => $allowedEppaList, 'statusText' => "Gartner access denied for ePPA \"${eppa}\" for user \"${username}\"" ]);
            }

            //usfEduCampus is the USF Campus
            $campus = $request->getHeaderLine('AUTH_ATTR_USFEDUCAMPUS') ?? '';
            //GivenName is the first name
            $firstName = $request->getHeaderLine('AUTH_ATTR_GIVENNAME') ?? '';        
            //Surname is the last name
            $lastName = $request->getHeaderLine('AUTH_ATTR_SURNAME') ?? '';
            // Mail is  the email address
            $email = $request->getHeaderLine('AUTH_ATTR_MAIL') ?? '';
            $gartnerURL = $this->authService->getRedirectUrl([
                'uid' => $username,
                'fn' => $firstName,
                'ln' => $lastName,
                'em' => $email,
                'title' => $eppa,
                'other' => $campus,
                'resId' => $resId,
                'docCode' => $docCode 
            ]);
            // The result was a URL for the Gartner Application
            $this->logger->info("REDIRECT|${username}|${gartnerURL}");
            return $response->withRedirect($gartnerURL);
        } else {
            $this->logger->error("Request for Gartner - Disabled|AUTHTOKEN_DISABLED"); 
            return $this->view->render($response, 'error.html', ['disabled' => TRUE, 'statusText' => "Requests for Gartner are disabled" ]);
        }
    }

}
