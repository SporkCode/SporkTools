<?php
namespace SporkTools\Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Header\ContentType;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function eventsAction()
    {
        $applicationEvents = $this->getEvent()->getApplication()->getEventManager(); 
        $moduleEvents = $this->getServiceLocator()->get('moduleManager')->getEventManager();
        return new ViewModel(array('eventManagers' => array(
            $moduleEvents,
            $applicationEvents,
        )));
    }
    
    public function phpInfoAction()
    {
        ob_start();
        phpinfo();
        $info = ob_get_clean();
        if (preg_match('`<style[^>]*>(.*)</style>.*<body[^>]*>(.*)</body>`is', $info, $matches)) {
            $style = $matches[1];
            $info = $matches[2];
            
            $style = trim($style);
            $style = explode(PHP_EOL, $style);
            foreach ($style as $index => $line) {
                $style[$index] = '#phpinfo ' . $line;
            }
            $style = implode(PHP_EOL, $style);
        } else {
            $style = '';
        }
        
        return array('style' => $style, 'info' => $info);
    }
    
    public function servicesAction()
    {
        $serviceMapper = new \SporkTools\Core\ServiceManager\Mapper($this->getServiceLocator());
        
        return array('serviceMapper' => $serviceMapper);
    }
    
    public function styleAction()
    {
        $response = $this->response;
        $response->getHeaders()->addHeader(new ContentType('text/css'));
        $response->setContent(file_get_contents(__DIR__ . '/../../static/sporktools.css'));
        return $response;
    }
}