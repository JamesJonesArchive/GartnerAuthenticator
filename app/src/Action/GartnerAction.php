<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace USF\IdM\AuthTransfer\Gartner\Action;

/**
 * Description of TestAction
 *
 * @author James Jones <james@mail.usf.edu>
 */
class GartnerAction extends \USF\IdM\AuthTransfer\BasicAuthServiceAction {
    
    public function dispatch(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        $view_attrs = [];
        $netid = $request->getHeaderLine('AUTH_PRINCIPAL');
        $ipAddress = $request->getAttribute('ip_address');
        $application = $args['application'] ?? 'UNKNOWN';
        $instance = $args['instance'] ?? $request->getQueryParams()['instance'] ?? $this->settings['defaultInstance'] ?? 'production';
        $deepLink = $request->getQueryParams()['uri'] ?? '';
        $selectUsername = $request->getQueryParams()['accountSelection'] ?? '';
        // Ensure the passed instance is configured
        if (!isset($this->settings['instances'][$instance])) {
            $view_attrs['statusText'] = 'Configuration Error';
            $view_attrs['message'] = "The Gartner instance <em><strong>${instance}</strong></em> is not configured in this application.";
            $this->logger->error("${application}|NO_INSTANCE_FOUND|${instance}|${ipAddress}|${netid}");
            return $this->view->render($response, 'error.html', $view_attrs);
        }
        // Ensure the passed application is configured
        if (!isset($this->settings['instances'][$instance][$application])) {
            $view_attrs['statusText'] = 'Configuration Error';
            $view_attrs['message'] = "The Gartner application <em><strong>${application}</strong></em> is not configured for authentication.";
            $this->logger->error("${application}|NO_APPLICATION_FOUND|${instance}|${ipAddress}|${netid}");
            return $this->view->render($response, 'error.html', $view_attrs);
        }
        try {
            $result = $this->authService->getRedirectUrl($application, $instance, $netid, $ipAddress, $deepLink, $selectUsername);
            // The result was a list of usernames
            if (is_array($result)) {
                $view_attrs['account_list'] = $result;
                $view_attrs['application']  = $application;
                $view_attrs['instance']     = $instance;
                $view_attrs['deepLink']     = $deepLink;
                return $this->view->render($response, 'select_account.html', $view_attrs);
            }
            // The result was a URL for the Gartner Application
            $this->logger->info("${application}|REDIRECT|${instance}|${ipAddress}|${netid}|${result}");
            return $response->withRedirect($result);
        } catch (NoAccountFoundException $e) {
            $view_attrs['message'] = $e->getMessage();
            $this->logger->warn("${application}|NO_ACCOUNT_FOUND|${instance}|${ipAddress}|${netid}");
            $this->view->render($response, 'account_error.html', $view_attrs);
        } catch (MultipleAccountsFoundException $e) {
            $view_attrs['message'] = $e->getMessage();
            $this->logger->warn("${application}|MULTIPLE_ACCOUNTS_FOUND|${instance}|${ipAddress}|${netid}");
            $this->view->render($response, 'account_error.html', $view_attrs);
        } catch (\Exception $e) {
            $view_attrs['statusText'] = 'Authentication Error';
            $view_attrs['message'] = $e->getMessage();
            $view_attrs['file'] = $e->getFile() . ':' . $e->getLine();
            $this->logger->warn("${application}|GENERIC_ERROR|${instance}|${ipAddress}|${netid}|${view_attrs['message']}");
            $this->view->render($response, 'error.html', $view_attrs);
        }
    }

}
