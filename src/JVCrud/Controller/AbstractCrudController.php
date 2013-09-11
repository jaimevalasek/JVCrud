<?php

namespace JVCrud\Controller;

use Zend\Session\Container;

use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\AbstractActionController;

abstract class AbstractCrudController extends AbstractActionController
{
    protected $service; // name of service to charge the service layer
    protected $form; // service to display the form
    protected $filter; // filter service to validate the form
    protected $route; // to $this->redirect()->toRoute($this->getRoute())
    protected $url; // to $this->redirect()->toUrl($this->getUrl())
    protected $controller; // to $this->redirect()->toRoute($this->getRoute(), array('controller' => $this->getController()))
    protected $viewForm; // path of the view to display the form * Require
    protected $additionalVars = array(); // additional variables to send to the view
    protected $setPaginator = true;
    protected $itemCountPerPage = 2;
    protected $sessionNav;
    
    public function indexAction()
    {
        $this->getServiceLocator()->get('jv_flashmessenger');
        $pagina = $this->params()->fromQuery('pagina') ?: 1;
        $filter = $this->params()->fromQuery();
        
        $sessionNav = $this->getSessionNav();
        $sessionNav->offsetSet('fromQuery', $this->params()->fromQuery());
        
        $crudFilterFilter = $this->getServiceLocator()->get('jvcrud-filter-crudfilter');
        $service = $this->getService();
        $esquemaFilter = $service->getPageFilter();

        // Se existe o esquema do filtro vai gerar o filtro caso esteja corretamente configurado
        $filterWhere = array();
        if (count($esquemaFilter)) {
            $filterWhere = $crudFilterFilter->extract($filter, $esquemaFilter);
        }
     
        if ($this->getSetPaginator()) {
            $service->usePaginator(array('itemCountPerPage' => $this->getItemCountPerPage(), 'currentPageNumber' => $pagina));
        }
        
        if (count($filterWhere)) {
            $data = $service->findAllBy($filterWhere, null, null, 'object');
        } else {
            $data = $service->findAll(null, 'object');
        }
        
        $pageFilter = array();
        if (isset($esquemaFilter['values'])) 
        {
            foreach ($esquemaFilter['values'] as $key => $value)
            {
                $pageFilter[$value] = isset($filter[$value]) ? $filter[$value] : "";
            }
        }
        
        /* echo "<pre>";
        exit(print_r($data->getTotalItemCount()));
        echo "</pre>"; */
        
        return new ViewModel(array(
            'data' => $data,
            'pagina' => $pagina,
            'filter' => $pageFilter,
            'additionalVars' => $this->getAdditionalVars()
        ));
    }

    public function newAction()
    {
        $form = $this->getForm();
        $filter = $this->getFilter();
        $request = $this->getRequest();
        $sessionNav = $this->getSessionNav();
        
        $paramsQuery = $this->params()->fromQuery() ?: $sessionNav->offsetGet('fromQuery');
        
        // pega a classe filter url e cria a url para redirecionar
        $urlFilter = $this->getServiceLocator()->get('jvcrud-filter-url');
        
        $fromQuery = $urlFilter->verifyUrlQueryFilter($paramsQuery);

        if ($request->isPost())
        {
            $form->setData($request->getPost());
            $form->setInputFilter($filter);
            	
            if ($form->isValid())
            {
                $service = $this->getService();
                $data = $form->getData();
                if ($service->insert($data)) 
                {
                    $this->flashMessenger()->addMessage(array('success' => 'Item cadastrado com sucesso!'));
                    
                    // Preferencia para redicional pela toRoute
                    if ($this->getRoute()) {
                        return $this->redirect()->toRoute($this->getRoute(), $this->getController());
                    }
                    
                    // Se não tiver rota redireciona pelo tUrl
                    if ($this->getUrl()) {
                        return $this->redirect()->toUrl($this->getUrl($fromQuery));
                    }
                }
            }
        }

        $view = new ViewModel(array(
            'form' => $form,
            'additionalVars' => $this->getAdditionalVars()
        ));
        $view->setTemplate($this->getViewForm());
        
        return $view;
    }
    
    public function editAction()
    {
        $id = (int) $this->params('id');
        $service = $this->getService();
        $form = $this->getForm();
        $filter = $this->getFilter();
        $request = $this->getRequest();
        
        $sessionNav = $this->getSessionNav();
        
        $paramsQuery = $this->params()->fromQuery() ?: $sessionNav->offsetGet('fromQuery');
        
        // pega a classe filter url e cria a url para redirecionar
        $urlFilter = $this->getServiceLocator()->get('jvcrud-filter-url');
        $fromQuery = $urlFilter->verifyUrlQueryFilter($paramsQuery);
        
        $item = $service->findById($id);
        $form->setData($item);
    
        if ($request->isPost())
        {
            $form->setData($request->getPost());
            $form->setInputFilter($filter);
             
            if ($form->isValid())
            {
                $data = $form->getData();
                if ($service->update($data, $id))
                {
                    $this->flashMessenger()->addMessage(array('success' => 'Item atualizado com sucesso!'));
                    
                    // Preferencia para redicional pela toRoute
                    if ($this->getRoute()) {
                        return $this->redirect()->toRoute($this->getRoute(), $this->getController());
                    }
    
                    // Se não tiver rota redireciona pelo tUrl
                    if ($this->getUrl()) {
                        return $this->redirect()->toUrl($this->getUrl($fromQuery));
                    }
                }
            }
        }
    
        $view = new ViewModel(array(
            'form' => $form,
            'additionalVars' => $this->getAdditionalVars()
        ));
        $view->setTemplate($this->getViewForm());
        
        return $view;
    }
    
    public function deleteAction()
    {
        $id = (int) $this->params('id');
        $service = $this->getService();
        $sessionNav = $this->getSessionNav();
        
        $paramsQuery = $this->params()->fromQuery() ?: $sessionNav->offsetGet('fromQuery');
        
        // pega a classe filter url e cria a url para redirecionar
        $urlFilter = $this->getServiceLocator()->get('jvcrud-filter-url');
        $fromQuery = $urlFilter->verifyUrlQueryFilter($paramsQuery);
    
        if ($service->delete($id)) {
            $this->flashMessenger()->addMessage(array('success' => 'Item excluído com sucesso!'));

            // Preferencia para redicional pela toRoute
            if ($this->getRoute()) {
                return $this->redirect()->toRoute($this->getRoute(), $this->getController());
            }
        
            // Se não tiver rota redireciona pelo tUrl
            if ($this->getUrl()) {
                return $this->redirect()->toUrl($this->getUrl($fromQuery));
            }
        }
    
        $this->flashMessenger()->addMessage(array('error' => 'O item não foi excluído'));
        
        // Preferencia para redicional pela toRoute
        if ($this->getRoute()) {
            return $this->redirect()->toRoute($this->getRoute());
        }
    
        // Se não tiver rota redireciona pelo tUrl
        if ($this->getUrl()) {
            return $this->redirect()->toUrl($this->getUrl());
        }
    
    }

    public function getService()
    {
        if ($this->service === null) {
            return false;
        }
        
        return $this->getServiceLocator()->get($this->service);
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getForm()
    {
        if ($this->form === null) {
            return false;
        }
        
        return $this->getServiceLocator()->get($this->form);
    }

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function getFilter()
    {
        if ($this->filter === null) {
            return false;
        }
        
        return $this->getServiceLocator()->get($this->filter);
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getUrl($fromQuery)
    {
        if ($this->url !== null && !empty($fromQuery))
        {
            return $this->url . $fromQuery;
        }
        
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getViewForm()
    {
        return $this->viewForm;
    }

    public function setViewForm($viewForm)
    {
        $this->viewForm = $viewForm;
    }

    public function getAdditionalVars()
    {
        if (is_array($this->additionalVars) && count($this->additionalVars)) {
            return $this->additionalVars;
        }
        
        return array();
    }

    public function setAdditionalVars($additionalVars)
    {
        $this->additionalVars = $additionalVars;
    }

	public function getRoute()
	{
	    if ($this->route === null) {
	        return false;
	    }
	    
	    return $this->route;
	}

	public function setRoute($route)
	{
	    $this->route = $route;
	}

	public function getController()
	{
	    if ($this->controller === null) {
	        return false;
	    }
	    
	    return array('controller' => $this->controller);
	}

	public function setController($controller)
	{
	    $this->controller = $controller;
	}

	public function getItemCountPerPage()
	{
	    return $this->itemCountPerPage;
	}

	public function setItemCountPerPage($itemCountPerPage)
	{
	    $this->itemCountPerPage = $itemCountPerPage;
	}

	public function getSetPaginator()
	{
	    return $this->setPaginator;
	}

	public function setSetPaginator($setPaginator)
	{
	    $this->setPaginator = $setPaginator;
	}

	public function getSessionNav()
	{
	    $this->sessionNav = new Container('nav');
	    return $this->sessionNav;
	}

	public function setSessionNav($sessionNav)
	{
	    $this->sessionNav = $sessionNav;
	}
}