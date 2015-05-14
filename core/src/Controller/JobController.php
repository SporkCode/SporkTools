<?php
namespace SporkTools\Core\Controller;

use SporkTools\Core\Job;
use Zend\Mvc\Controller\AbstractActionController;
use SporkTools\Core\Job\Feature\OutputCollection;
use SporkTools\Core\Job\Schedule;

class JobController extends AbstractActionController
{
    public function indexAction()
    {
        $manager = $this->getJobManager();
        $jobs = array();
        foreach ($manager->getJobs() as $job) {
            $report = $job->getReport();
            if (null === $report) {
                $last = null;
                $messages = array();
            } else {
                $last = $report->getLastRun();
                $messages = $report->toArray();
            }
            $schedule = $job->getSchedule();
            $next = $schedule->getNext();
            
            $jobs[] = array(
                'id'        => $job->getId(),
                'name'      => $job->getName(),
                'last'      => null === $last ? 'NA' : $last->format('Y-m-d G:i:s T'),
                'next'      => null === $next ? 'NA' : $next->format('Y-m-d G:i:s T'),
                'schedule'  => $schedule->getDescription(),
                'messages'  => $messages,
            );
        }
        return array('manager' => $manager, 'jobs' => $jobs);
    }
    
    public function editAction()
    {
        $manager = $this->getJobManager();
        $id = $this->params('job');
        if (null === $id) {
            $job = new Job\Job();
            $job->setEventManager($manager->getEventManager());
            $job->setServiceManager($this->serviceLocator);
        } else {
            $job = $manager->getJob($id);
        }
        
        if ($this->request->isPost()) {
            $job->setName($this->request->getPost('name'));
            $tasks = array();
            foreach ($this->request->getPost('tasks', array()) as $code) {
                $code = trim($code);
                if (!empty($code)) {
                    $tasks[] = new Job\Task($code);
                }
            }
            $job->setTasks($tasks);
            $job->save();
            return $this->redirect()->toRoute('spork-tools/job');
        }
        
        return array('job' => $job);
    }
    
    public function scheduleAction()
    {
        $manager = $this->getJobManager();
        $job = $manager->getJob($this->params('job'));
        
        if ($this->request->isPost()) {
            $intervals = $this->request->getPost('intervals');
            $offsets = $this->request->getPost('offsets'); 
            $schedule = new Schedule();
            for ($index = 0; $index < count($intervals); $index++) {
                $interval = null == $intervals[$index] ? 0 : $intervals[$index];
                $offset = null == $offsets[$index] ? 0 : $offsets[$index];
                if (0 != $interval || 0 != $offset) {
                    $schedule->addTime($interval, $offset);
                }
            }
            $job->setSchedule($schedule);
            $job->save();
            return $this->redirect()->toRoute('spork-tools/job');
        }
        
        $schedule = $job->getSchedule();
        return array('job' => $job, 'schedule' => $schedule);
    }
    
    public function runAction()
    {
        $manager = $this->getJobManager();
        $job = $manager->getJob($this->params('job'));
        $job->run();
        $job->save();   // save report it possible
        $messages = $job->getReport()->getMessages();
        
        return array('job' => $job, 'messages' => $messages);
    }
    
    public function deleteAction()
    {
        $job = $this->getJobManager()->getJob($this->params('job'));
        $job->delete();
        return $this->redirect()->toRoute('spork-tools/job');
    }
    
    /**
     * @return \SporkTools\Core\Job\Manager
     */
    protected function getJobManager()
    {
        return $this->serviceLocator->get(Job\ServiceFactory::SERVICE);
    }
}