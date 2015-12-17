<?php
class applicationsGadget extends GadgetAbstractGadget {
    public function onInit(){
        $this->setDefaultView("simplelist");
        $this->registerCachedAction("regional",3600,"xml");
        $this->registerCachedAction("disciplines",3600,"xml");
        $this->registerCachedAction("vos",3600,"xml");
    }
    public function idAction($p){
        $res = $this->AppDBrestAPI->applications($p["id"]);
        return $this->xmlView($res);
    }
    public function listAction($p){
       $res = $this->AppDBrestAPI->applications($p);
       return $this->xmlView($res);
    }
    public function queryAction($p){
        $res = $this->AppDBrestAPI->applications($p);
        return $this->xmlView($res);
    }
    public function regionalAction(){
        $res = $this->AppDBrestAPI->Regional();
        return $this->xmlView($res);
    }
    public function disciplinesAction(){
        $res = $this->AppDBrestAPI->Desciplines();
        return $this->xmlView($res);
    }
    public function vosAction(){
        $res = $this->AppDBrestAPI->VOs();
        return $this->xmlView($res);
    }
	public function tagsAction(){
		$res = $this->AppDBrestAPI->Tags();
		return $this->xmlView($res);
	}
	public function categoriesAction(){
		$res = $this->AppDBrestAPI->Categories();
		return $this->xmlView($res);
	}
}
?>
