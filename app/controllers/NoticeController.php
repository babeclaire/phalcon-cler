<?php
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
 use \Phalcon\Paginator\Adapter\QueryBuilder as PaginacionBuilder;

class NoticeController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

         $notice = $this->modelsManager->createBuilder()
        ->from('Notice')
        ->WHERE('soft_delete =false')
        ->orderBy('id');

        $paginator = new PaginacionBuilder(array(
            "builder" => $notice,
            "limit"=> 5,
            "page" => $this->request->getQuery('page', 'int')
        ));
        
        //pasamos el objeto a la vista con el nombre de $page
        $this->view->page = $paginator->getPaginate();
    }
    public function addAction()
    {

    }
    public function createAction()
    {
        $notice = new Notice();
        $notice->title = $this->request->getPost("title");
        $notice->description = $this->request->getPost("description");
        $notice->author= $this->request->getPost("author");
        $notice->created_date =date("Y-m-d H:i:s");
        if (!$notice->save()) {
            foreach ($notice->getMessages() as $message) {
                $this->flash->error($message);
            }
            $this->dispatcher->forward([
                'controller' => "notice",
                'action' => 'add'
            ]);

            return;
        }
        $this->flash->success("Notice was added successfully");
        $this->dispatcher->forward([
            'controller' => "notice",
            'action' => 'index'
        ]);
    }
        public function editAction($id)
    {
        if (!$this->request->isPost()) {
            $notice = Notice::findFirstByid($id);
            if (!$notice) {
                $this->flash->error("notice was not found");

                $this->dispatcher->forward([
                    'controller' => "notice",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $notice->id;
            $this->tag->setDefault("id", $notice->id);
            $this->tag->setDefault("title", $notice->title);
            $this->tag->setDefault("description", $notice->description);
            $this->tag->setDefault("author", $notice->author);
            
        }
    }
     public function saveAction()
    {
        $id = $this->request->getPost("id");
        $notice = Notice::findFirstByid($id);
        $notice->title = $this->request->getPost("title");
        $notice->description = $this->request->getPost("description");
        $notice->author = $this->request->getPost("author");
        if (!$notice->save()) {
            foreach ($notice->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "notice",
                'action' => 'edit',
                'params' => [$notice->id]
            ]);

            return;
        }
        $this->flash->success("Notice was updated successfully");

        $this->dispatcher->forward([
            'controller' => "notice",
            'action' => 'index'
        ]);
    }
      public function readAction($id)
    {

            $notice = Notice::findFirstByid($id);
            $this->view->id = $notice->id;
            $this->tag->setDefault("id", $notice->id);
            $this->tag->setDefault("title", $notice->title);
            $this->tag->setDefault("description", $notice->description);
            $this->tag->setDefault("author", $notice->author);
            $this->tag->setDefault("created_date", $notice->created_date);
            $this->tag->setDefault("updated_date", $notice->updated_date);
            
        
    }
     public function deleteAction($id)
    {
        $notice = Notice::findFirstByid($id);
        $notice->soft_delete = 1;
        if (!$notice) {
            $this->flash->error("Notice was not found");
            $this->dispatcher->forward([
                'controller' => "notices",
                'action' => 'index'
            ]);

            return;
        }
        if (!$notice->update()) {

            foreach ($notice->getMessages() as $message) {
                $this->flash->error($message);
            }
            $this->dispatcher->forward([
                'controller' => "notice",
                'action' => 'index'
            ]);

            return;
        }
        $this->flash->success("Notice was deleted successfully");
        $this->dispatcher->forward([
            'controller' => "notice",
            'action' => "index"
        ]);
    }
 public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Notice', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $notice = Notice::find($parameters);
        if (count($notice) == 0) {
            $this->flash->notice("The search did not find any notice");

            $this->dispatcher->forward([
                "controller" => "notice",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $notice,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

}

