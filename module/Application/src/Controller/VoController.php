<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
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
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class VoController extends AbstractActionController
{
    protected $vofile;
    protected $xml;

//// OBSOLETE, moved to SitesController    
//	private function makeVAprovidersCache() {
//		$copyfile = \RestAPIHelper::getFolder(\RestFolderEnum::FE_CACHE_FOLDER) . '../../public/assets/rp/va_providers.xml';
//		$hashfile = \RestAPIHelper::getFolder(\RestFolderEnum::FE_CACHE_FOLDER) . '../../public/assets/rp/datahash';
//
//		# truncate data hash file (i.e. sync operation in progress)
//		$f_hashfile = @fopen($hashfile, "w");
//		if ($f_hashfile !== false) { 
//			fwrite($f_hashfile, "");
//			fclose($f_hashfile);
//		} else {
//			$errors = error_get_last();
//			error_log("[makeVAprovidersCache] Could not open+truncate VA providers cache data hash file. Reason: " . $errors['message']);
//		}
//		$uri = 'https://' . $_SERVER['$_SERVER['APPLICATION_API_HOSTNAME']'] . '/rest/latest/va_providers?listmode=details';
//		$ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $uri);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//		if ( defined('CURLOPT_PROTOCOLS') ) curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
//		$headers = apache_request_headers();
//		$h = array();
//		$h["Expect"] = '';
//		if ( isset($headers['Accept-Encoding']) ) $h['Accept-Encoding'] = $headers['Accept-Encoding'];
//		foreach($h as $k => $v) {
//			$h[] = "$k: $v";
//		}
//		$h['Connection']='Keep-Alive';
//		$h['Keep-Alive']='300';		
//		curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
//		// remove existing cachefiles before making API call, or else this will not work
//		foreach ( glob(\RestAPIHelper::getFolder(\RestFolderEnum::FE_CACHE_FOLDER) .'/query_RestVAProvidersList_*.xml') as $cachefile ) {
//			@unlink($cachefile);
//		}
//		error_log('VA providers RESTful API XML cache STARTED');
//		// 1st call creates the cache
//		$result = curl_exec($ch);
//		error_log('VA providers RESTful API XML cache DONE');		
//		// 2nd call returns the cached response
//		$result = curl_exec($ch);
//		$result_tmp = @gzdecode($result);
//		if ($result_tmp !== false) {
//			$result = $result_tmp;
//		}
//		$ck = "";
//		try {
//			$xmlresult = new \SimpleXMLElement($result);
//			$appdb = $xmlresult->xpath("//appdb:appdb");
//			$vadata = $xmlresult->xpath("//virtualization:provider");
//			$vadatastring = "";
//			foreach ($vadata as $vadatum) {
//				$vadatastring .= $vadatum->asXML();
//			}
//			$hash = md5($vadatastring);
//			if (count($appdb) > 0) {
//				$appdb = $appdb[0];
//				$ck = trim(strval($appdb->attributes()->cachekey));
//				if ($ck != "") {
//					debug_log("[makeVAprovidersCache] cache key is " . $ck);
//				} else {
//					error_log("[makeVAprovidersCache] Did not find cache key in XML response");
//				}
//			} else {
//				error_log("[makeVAprovidersCache] Could not find appdb:appdb root element in XML response");
//			}
//		} catch (Exception $e) {
//			error_log("[makeVAprovidersCache] Could not parse respone as XML. Reason: " . $e->getMessage());
//		}
//		curl_close($ch);
//		if ($ck != "") {
//			if (!@copy(\RestAPIHelper::getFolder(\RestFolderEnum::FE_CACHE_FOLDER) .'/query_RestVAProvidersList_' . $ck . '.xml', $copyfile)) {
//				$errors = error_get_last();
//				error_log("[makeVAprovidersCache] Could not copy VA providers cache file into assets. Reason: " . $errors['message']);
//			} else {
//				debug_log("Copied VA providers cache file into assets");
//				// XML cache file has been copied to assets. Create a JSON copy as well.
//				$copyfile2 = str_replace(".xml", ".json", $copyfile);
//				$jsondata = \RestAPIHelper::transformXMLtoJSON(file_get_contents($copyfile));
//				$f_jsonop = true;
//				$f_jsonfile = @fopen($copyfile2, "w");
//				if ($f_jsonfile !== false) {
//					if (@fwrite($f_jsonfile, "" . $jsondata) === false) {
//						$errors = error_get_last();
//						error_log("[makeVAprovidersCache] Could not write to VA providers cache file JSON copy in assets. Reason: " . $errors['message']);
//						$f_jsonop = false;
//					}
//					@fclose($f_jsonfile);
//				} else {
//						$errors = error_get_last();
//						error_log("[makeVAprovidersCache] Could not open VA providers cache file JSON copy for writing in assets. Reason: " . $errors['message']);
//						$f_jsonop = false;
//				}
//				if ($f_jsonop) {
//					debug_log("Created VA providers cache file JSON copy in assets");
//				}
//				// Keep a hashfile of the cache
//				$f_hashop = true;
// 				$f_hashfile = @fopen($hashfile, "w");
// 				if ($f_hashfile !== false) {
//					if (@fwrite($f_hashfile, $hash) === false) {
//						$errors = error_get_last();
//						error_log("[makeVAprovidersCache] Could not write to VA providers cache data hash file. Reason: " . $errors['message']);
//						$f_hashop = false;
//					}
//					@fclose($f_hashfile);
// 				} else {
// 					$errors = error_get_last();
//					error_log("[makeVAprovidersCache] Could not open VA providers cache data hash file for writing. Reason: " . $errors['message']);
//					$f_hashop = false;
// 				}
// 				debug_log("Data md5 is $hash");
//				if ($f_hashop) {
//					debug_log("Copied VA providers cache hash file into assets");
//				}
//			}
//		} else {
//			error_log("[makeVAprovidersCache] No VA providers cache file to copy into assets");
//			$f_hashfile = @fopen($hashfile, "w");
//			@fwrite("ERROR", $hash);
//			fclose($f_hashfile);
//		}
//	}

    public function __construct()
    {
        /* Initialize action controller here */
		$this->view = new ViewModel();
		$this->session = new \Zend\Session\Container('base');
		$this->vofile = $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vos.xml";
		$this->xml = $this->fetchVOs();
	}

	public function refreshAction() {
	    $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		// Prevent malicious calls
		if ( localRequest() ) {
			$this->syncVOs();
			@exec($_SERVER['APPLICATION_PATH'] . "/../bin/appdb-montage-vo-logos");
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
		}
	}

    private function printError() 
	{
		DISABLE_LAYOUT($this);
        return SET_NO_RENDER($this, '<div style="padding: 50px; text-align: left; vertical-align: middle; height: 100%"><div style="text-align: center"><img width="32px" src="/images/error.png"/></div><span><h3>There was an error fetching VO data from the <a href="http://operations-portal.egi.eu" target="_blank">EGI Operations Portal</a>. The service may be down, or experiencing network problems. If this error persists for more than an hour, please <a href="http://helpdesk.egi.eu/" target="_blank">let us know</a>.</h3></span></div>', 200);
    }

    private function paging(&$entries, $offset, $length, $total) {
        $this->view->lastPage=false;
        $segment = array();
        for($i=$offset; $i<=$offset+$length; $i++) {
                if ( $i > count($entries)-1 ) {
                        $this->view->lastPage=true;
                        break;
                }
                array_push($segment,$entries[$i]);
        }
        $this->view->entries = $segment;
        $this->view->offset = $offset;
        $this->view->length = $length;
        $this->view->pageCount = ceil($total / ($length+1));
        $this->view->currentPage = floor($offset / ($length+1));
        $this->view->total = $total;
    }

    public function getlogoAction()
    {
		$name = GET_REQUEST_PARAM($this, 'name');
		$discipline = GET_REQUEST_PARAM($this, 'id');
		$vid = GET_REQUEST_PARAM($this, 'vid');
		
		if( trim($name) !== "" && strtolower( trim($name) ) === "eubrazilcc.eu" ) {
			$img = "images/vo_eubrazilcc_eu.png";
		} else {
			if ($vid != '') {
				$img = db()->query("SELECT vos.logoid FROM vos WHERE id = $vid", array())->toArray();
				if (count($img) > 0) {
					$img = $img[0];
					if (is_array($img)) {
						if (count($img) > 0) {
							$img = $img['logoid'];
						}
					}
				} else {
					$img = "0";
				}
			} else {
				$img = "0";
			}		
			$img = $_SERVER['APPLICATION_PATH'] . "/../../public/images/disciplines/$img.png";
		}
		header('PRAGMA: NO-CACHE');
		header('CACHE-CONTROL: NO-CACHE');
		header('Content-type: image/png');
		if (file_exists($_SERVER['APPLICATION_PATH'] . "/../../public/" . $img)) {
			readfile($img);	
		} else {
			readfile($_SERVER['APPLICATION_PATH'] . "/../../public/images/disciplines/0.png");	
		}
		DISABLE_LAYOUT($this);
		return SET_NO_RENDER($this);
	}

    public function resourcesAction()    
    {
        $voname = GET_REQUEST_PARAM($this, "id");
        if ($voname != null) {
            $xml = new \SimpleXMLElement($this->xml);
			$volist = $xml->xpath("//VoDump/IDCard[translate(@Name,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')='".strtoupper($voname)."']");
	    	if (count($volist)>0) {
                $r=$volist[0]->Ressources;
                $res=array(
                    'RAM/i386 core' => $r->RAM_per_i386_Core,
                    'RAM/x86_64 core' => $r->RAM_per_x86_64_Core,
                    'Scratch space for jobs' => $r->JobScratchSpace,
                    'Max CPU time for jobs' => $r->JobMaxCPUTime,
                    'Max wall clock time for jobs' => $r->JobMaxWallClockTime,
                    'Notes' => $r->OtherRequirements
                );
                $this->view->entry = $res;
            }
		}
		return DISABLE_LAYOUT($this);
    }


	private function parseDisc($discs, $lvl = 1, $pid = "") {
		$ds = array();
		if (!$discs) {
			return $ds;
		}
		$discs = $discs->xpath("./level" . $lvl);
		foreach($discs as $disc) {
			$att = $disc->attributes();
			$d = array();
			$d["name"] = strval($att["key"]);
			if (($lvl > 1) && ($pid != "")) {
				$res = db()->query("SELECT * FROM htree('disciplines', '', 0, '') AS h WHERE h.name = ? AND parentid = ? AND h.lvl = ?", array(strval($att["key"]), $pid, $lvl))->toArray();
			} else {
				$res = db()->query("SELECT * FROM htree('disciplines', '', 0, '') AS h WHERE h.name = ? AND h.lvl = ?", array(strval($att["key"]), $lvl))->toArray();
			}
			if (count($res) > 0) {
				$res = $res[0];
			} else {
				$res = null;
			}
			if ($res != null) {
				$d["id"] = "" . $res["id"];
				$d["parentid"] = "" . $res["parentid"];
			} else {
				$d["id"] = "0";
				$d["parentid"] = "0";
			}
			$res = db()->query("SELECT ord FROM disciplines WHERE id = ?", array($d["id"]))->toArray();
			if (count($res) > 0) {
				$res = $res[0];
				$d["order"] = "" . $res["ord"];
			} else {
				$d["order"] = "1";
			}
			if ($d["name"] != "") {
				$ds[] = $d;
			}
			if ($lvl < 10) {
				$_pid = $d["parentid"];
				if ($_pid == "0") {
					$_pid = "";
				}
				$_vos = $this->parseDisc($disc, $lvl + 1, $_pid);
				if (count($_vos) > 0) {
					$ds = array_merge($ds, $_vos);
				}
			}
		}
		return $ds;
	}

	private function populateVO(&$voentry)
	{
		$vo = new \Application\Model\VO2();
		$att = $voentry->attributes();
		$vo->name = $att["Name"];
		$vo->serial = $att["Serial"];
		$vo->alias = $att["Alias"];
		$vo->description = trim(strval($voentry->Description));
		$discs = array();
		$discnames = array();
		$minid = -1;
		$minname = "Other";
		$xdiscs = $voentry->xpath("./Disciplines");
		if (count($xdiscs) > 0) {
			$discs = $this->parseDisc($xdiscs[0]);
			foreach ($discs as $d) {
				if ($minid == -1 || $minid > $d["id"]) {
					$minid = $d["id"];
					$minname = $d["name"];
				}
				$discnames[] = $d["name"];
			}
		}
		$vo->disciplines = $discs;
		//error_log(var_export($discs, true));
		$vo->discipline = $minname;
		$vo->discipline = array("domain" => $voentry->Discipline, "disciplines" => json_encode($discs));
		if ($vo->discipline == '') $vo->discipline = "Other";
		$vo->homepageUrl= $voentry->HomepageUrl;
		$vo->enrollmentUrl = $voentry->EnrollmentUrl;
		$vo->validationDate = $voentry->ValidationDate;
		$vo->scope = $voentry->Scope;
		$vo->contacts = $voentry->Contacts;
		$vo->supportproc = $voentry->SupportProcedure;
		$vo->aup = $voentry->AUP;
		if (isset($voentry->Middlewares)) {
			$ms = $voentry->Middlewares->attributes();
			foreach ($ms as $k=>$v) {
				if ($v=="1") $vo->middlewares[]=$k;
			}
		}

		return $vo;
    }

    private function validateXMLCache(&$xml, $vofile = null)
	{
		if (is_null($vofile)) $vofile = $this->vofile;
        $valid=true;
        try {
            $valid = simplexml_load_file($vofile);
        } catch (Exception $e) {
            $valid = false;
        }
        if ($valid === false) {
            $xml = null;
        } else {
            $x = new \SimpleXMLElement($xml);
            $volist = $x->xpath('//VoDump');
            if (count($volist)==0 || (count($volist)==1 && strlen($x->AsXML()) <= 40)) $xml=null;
        }
        if ($xml === null) {
		error_log("Invalid XML cache when syncing VOs");
		return false; 
	} else {
		return true;
	}
	}

	private function syncVOs() {
		$synced1 = $this->syncEGIVOs();
		$synced2 = $this->syncEBIVOs();

		if (($synced1 !== false) || ($synced2 !== false)) {
			// give precedence to EBI over EGI
			debug_log("Updating database to give precedence to EBI over EGI VOs...");
			db()->query("UPDATE vos SET deleted = TRUE WHERE sourceid = 1 AND LOWER(name) IN (SELECT LOWER(name) FROM perun.vos)");
			debug_log("Updating database to give precedence to EBI over EGI VOs [DONE]");
		}

		try {
			// aggregate VOs XML from all sources into one file, giving precedence to EBI
			$data1 = file_get_contents($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vos.xml");
			$xml1 = new \SimpleXMLElement($data1);
			$data2 = file_get_contents($_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivos.xml");
			if ( trim($data2) != "" ) {
				$xf = \RestAPIHelper::getFolder(\RestFolderEnum::FE_XSL_FOLDER).'ebi_to_egi_vos.xsl';
				$xsl = new DOMDocument();
				$xsl->load($xf);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$xml2 = new DOMDocument();
				$xml2->loadXML($data2, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$data2 = $proc->transformToXml($xml2);
				$xml2 = new \SimpleXMLElement($data2);
			}
			$f = fopen($_SERVER['APPLICATION_PATH'] . "/../../data/cache/aggvos.xml", "w");
			if ($f !== false) {
				fwrite($f, "<VoDump>\n");
				if ( trim($data2) != "" ) {
					$xp = $xml2->xpath("//IDCard");
					foreach ($xp as $x) {
						fwrite($f, str_replace('<' . '?xml version="1.0"?'.'>', "", $x->asXML()));
					}
				}
				$xp = $xml1->xpath("//IDCard");
				foreach ($xp as $x) {
					$xattr = $x->attributes();
					$xp2 = $xml2->xpath("//IDCard[@Name='" . $xattr["Name"] . "']");
					if (count($xp2) == 0) {
						fwrite($f, str_replace('<' . '?xml version="1.0"?'.'>', "", $x->asXML()));
					}
				}
				fwrite($f, "\n</VoDump>");
				fclose($f);
			}
		} catch (Exception $e) {
			error_log("Error while post-processing VO sync operation: $e");
		}
		error_log("Normalizing VOs [START]");
		db()->query("REFRESH MATERIALIZED VIEW normalized_vos;");
		error_log("Normalizing VOs [DONE]");
	}

	private function gridops_is_down() {
		@exec($_SERVER['APPLICATION_PATH'] . "/../../bin/gridops_down");
		if (file_exists(\RestAPIHelper::getFolder(\RestFolderEnum::FE_CACHE_FOLDER) . "/gridops_downtime")) {
			return true;
		} else {
			return false;
		}
	}

	private function syncEBIVOs() {
		$rs = db()->query("SELECT id,name, url, enabled FROM vo_sources WHERE name = 'EBI-Perun'", array())->toArray();
		$enabled = false;
		$uri = null;
		if (count($rs) > 0) {
			$rs = $rs[0];
			if (filter_var($rs['enabled'], FILTER_VALIDATE_BOOLEAN) === true) $enabled = true;
			$uri = $rs['url'];
		}
		if (! $enabled) {
			error_log("EBI-Perun VO source is disabled; will not sync");
			\ExternalDataNotification::sendNotification('VO::syncEBIVOs', "EBI-Perun VO source is disabled; will not sync", \ExternalDataNotification::MESSAGE_TYPE_ERROR);
			return false;
		}
		$inTransaction = false;
		$vofile = $_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivos.xml";
		try {
			if ( $_SERVER['APPLICATION_ENV]'] == "production" ) {
				// get entries
				$ch = curl_init();
				if (is_null($uri)) $uri = "https://perun.cesnet.cz/external/appdb/vos";
				curl_setopt($ch, CURLOPT_URL, $uri);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				//curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
				curl_setopt($ch, 181, 1 | 2);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
				curl_setopt($ch, CURLOPT_SSLCERT, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/usercert.pem');
				curl_setopt($ch, CURLOPT_SSLKEY, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/userkey.pem');
				$headers = apache_request_headers();
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$xml = curl_exec($ch);

				if ( $xml === false ) {
					error_log("error in syncEBIVOs: " . var_export(curl_error($ch), true));
					throw new Exception( var_export(curl_error($ch), true));
				} else {
					$xml = "<VoDump>$xml</VoDump>";
				}
				@curl_close($ch);
				
				// sort entries
				$xf = $_SERVER['APPLICATION_PATH'] . '/../../bin/sort_vos.xsl';
				$xsl = new DOMDocument();
				$xsl->load($xf);
				$proc = new XSLTProcessor();
				$proc->registerPHPFunctions();
				$proc->importStylesheet($xsl);
				$xml2 = new DOMDocument();
				$xml2->loadXML($xml, LIBXML_NSCLEAN | LIBXML_COMPACT);
				$xml = $proc->transformToXml( $xml2 );

				// cache entries
				@exec("rm ". $vofile . ".old");
				@exec("cp " . $vofile . " " . $vofile . ".old");
				$f = fopen($vofile,"w");
				fwrite($f,$xml);
				fclose($f);
			} else {
				$xml = "<VoDump>" . file_get_contents($vofile) . "</VoDump>";
			}
			if (($_SERVER['APPLICATION_ENV]'] != "production") || (($this->validateXMLCache($xml, $vofile)) && (@md5_file($vofile) !== @md5_file($vofile . ".old")))) {
				error_log("Sync'ing EBI VOs...");
				db()->beginTransaction();
				$inTransaction = true;
				db()->query("ALTER TABLE vos DISABLE TRIGGER tr_vos_99_refresh_permissions");
				db()->query("ALTER TABLE vos DISABLE TRIGGER rtr__vos_cache_delta");
				db()->query("ALTER TABLE perun.vo_contacts DISABLE TRIGGER tr_perun_vo_contacts_99_refresh_permissions");
				db()->query("DELETE FROM perun.vo_contacts");	// will be repopulated later on
				db()->query("DELETE FROM perun.vos");
				$xmlobj = new \SimpleXMLElement($xml);
                $xvos = $xmlobj->xpath("//VoDump/IDCard");
                // add new VOs and update existing VOs
				foreach($xvos as $xvo) {
					$att = $xvo->attributes();
					$vd = strval($xvo->validationDate);
					if (substr($vd, 0, 4) === "0000") {
						$vd = null;
					}
					db()->query("INSERT INTO perun.vos (name, validated, description, homepage, enrollment, alias, status) VALUES (?, ?, ?, ?, ?, ?, ?)", array(strtolower(trim($att["name"])), $vd, trim($xvo->description), trim($xvo->homepageUrl), trim($xvo->enrollmentUrl), trim($att["alias"]), trim($att["status"])));
				}
				db()->query("INSERT INTO vos (name,domainid,validated,description,homepage,enrollment,alias,status,sourceid) SELECT name,3,validated,description,homepage,enrollment,alias,status,(SELECT id FROM vo_sources WHERE name = 'EBI-Perun') AS sourceid FROM perun.vos AS x WHERE NOT EXISTS (SELECT * FROM vos AS y WHERE y.name = x.name AND y.sourceid = (SELECT id FROM vo_sources WHERE name = 'EBI-Perun'))");
				// mark missing EBI VOs as deleted
				db()->query("UPDATE vos SET deleted = TRUE WHERE sourceid = 2 AND LOWER(name) NOT IN (SELECT LOWER(name) FROM perun.vos)");
				// un-delete restored EBI VOs
				db()->query("UPDATE vos SET deleted = FALSE, deletedon = NULL WHERE sourceid = 2 AND LOWER(name) IN (SELECT LOWER(name) FROM perun.vos)");
				foreach($xvos as $xvo) {
					$att = $xvo->attributes();
					// sync vo / contacts relations.
					$xcontacts = $xvo->xpath("./contacts[@name='individuals']/contact");
					foreach( $xcontacts as $xcontact ) {
						$xdns = $xcontact->dn;
						$dns = array();
						$cas = array();
						foreach ($xdns as $xdn) {
							$dnatt = $xdn->attributes();
							$dns[] = str_replace("'", '’', trim(strval($xdn)));
							$cas[] = trim(strval($dnatt["ca"]));
						}
						$dns = php_to_pg_array($dns, false);
						$cas = php_to_pg_array($cas, false);
						if ($dns === '{}') $dns = null;
						if ($cas === '{}') $cas = null;
						$xeppns = $xcontact->eppn;
						$eppns = array();
						foreach ($xeppns as $xeppn) {
							$eppns[] = trim(strval($xeppn));
						}
						$eppns = php_to_pg_array($eppns, false);
						if ($eppns === '{}') $eppns = null;
						db()->query("INSERT INTO perun.vo_contacts (vo, name, role, email, dn, ca, sso, eppn) VALUES (?,?,?,?,(?)::text[],(?)::text[],?,(?)::text[]);", array(strtolower(trim($att["name"])), str_replace("'", '’', trim($xcontact->name)), trim($xcontact->role), trim($xcontact->email), $dns, $cas, trim($xcontact->sso), $eppns));
					}
				}
				db()->commit();
				db()->query("ALTER TABLE vos ENABLE TRIGGER tr_vos_99_refresh_permissions");
				db()->query("ALTER TABLE perun.vo_contacts ENABLE TRIGGER tr_perun_vo_contacts_99_refresh_permissions");
				db()->query("ALTER TABLE vos ENABLE TRIGGER rtr__vos_cache_delta");
				db()->query("SELECT rebuild_fulltext_index('vos');");
				db()->query("NOTIFY clean_cache;");
				db()->query("SELECT request_permissions_refresh()");
				error_log("EBI VOs sync'ed");
			} else {
				// no need to sync
				return false;
			}
		} catch (Exception $e) {
			$xml = false;
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
			}
			db()->query("ALTER TABLE vos ENABLE TRIGGER tr_vos_99_refresh_permissions");
			db()->query("ALTER TABLE perun.vo_contacts ENABLE TRIGGER tr_perun_vo_contacts_99_refresh_permissions");
			db()->query("ALTER TABLE vos ENABLE TRIGGER rtr__vos_cache_delta");
			db()->query("SELECT request_permissions_refresh()");
			error_log('Error while syncing EBI VOs: '.$e);
			\ExternalDataNotification::sendNotification('VO::syncEBIVOs', $e->getMessage(), \ExternalDataNotification::MESSAGE_TYPE_ERROR);
		}
		return $xml;
	}

	private function syncEGIVOs() {
		if ($this->gridops_is_down()) {
			error_log("EGI Operations portal is in downtime. EGI VO sync aborted");
			\ExternalDataNotification::sendNotification('VO::syncEGIVOs', "EGI Operations portal is in downtime. EGI VO sync aborted", \ExternalDataNotification::MESSAGE_TYPE_ERROR);
			return false;
		}
		$rs = db()->query("SELECT id, name, enabled, url FROM vo_sources WHERE name = 'EGI Operations Portal'", array())->toArray();
		$enabled = false;
		$uri = null;
		if (count($rs) > 0) {
			$rs = $rs[0];
			if (filter_var($rs['enabled'], FILTER_VALIDATE_BOOLEAN) === true) $enabled = true;
			$uri = $rs['url'];
		}
		if (! $enabled) {
			error_log("EGI Operations Portal VO source is disabled; will not sync");
			\ExternalDataNotification::sendNotification('VO::syncEGIVOs', "EGI Operations Portal VO source is disabled; will not sync", \ExternalDataNotification::MESSAGE_TYPE_ERROR);
			return false;
		};
		$inTransaction = false;
		try {
			// get entries
			$ch = curl_init();
			if (is_null($uri)) $uri = "http://operations-portal.egi.eu/xml/voIDCard/all/true";
			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, 181, 1 | 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
			curl_setopt($ch, CURLOPT_SSLCERT, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/usercert.pem');
			curl_setopt($ch, CURLOPT_SSLKEY, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/userkey.pem');
			$headers = apache_request_headers();
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$xml = curl_exec($ch);
			
			if ( $xml === false ) {
				error_log("error in syncEGIVOs: " . var_export(curl_error($ch), true));
				\ExternalDataNotification::sendNotification('VO::syncEGIVOs', var_export(curl_error($ch), true), \ExternalDataNotification::MESSAGE_TYPE_ERROR);
				return false;
			}
			@curl_close($ch);

			// sort entries
			$xf = $_SERVER['APPLICATION_PATH'] . '/../../bin/sort_vos.xsl';
			$xsl = new DOMDocument();
			$xsl->load($xf);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml2 = new DOMDocument();
			$xml2->loadXML($xml, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$xml = $proc->transformToXml( $xml2 );

/* NOT needed since the EGI OPS VO dump XML schema change
*
			// convert sciclass IDs to discipline IDs
			$xsl = new DOMDocument();
			$xsltable = db()->query('SELECT array_to_string(array_agg(\'<xsl:when test=". = \' || sciclassid::text || \'"><xsl:text>\' || disciplineid::text || \'</xsl:text></xsl:when>\'),E\'\n\') FROM disc_to_sciclass;', array())->toArray();
			$xsltable = $xsltable[0];
			$xsltable = $xsltable[0];
			$xsltable2 = db()->query('SELECT array_to_string(array_agg(\'<xsl:when test=". = \' || sciclassid::text || \'"><xsl:text>\' || parentid::text || \'</xsl:text></xsl:when>\'),E\'\n\') FROM disc_to_sciclass;', array())->toArray();
			$xsltable2 = $xsltable2[0];
			$xsltable2 = $xsltable2[0];
			$xsltable3 = db()->query('SELECT array_to_string(array_agg(\'<xsl:when test=". = \' || sciclassid::text || \'"><xsl:text>\' || ord::text || \'</xsl:text></xsl:when>\'),E\'\n\') FROM disc_to_sciclass;', array())->toArray();
			$xsltable3 = $xsltable3[0];
			$xsltable3 = $xsltable3[0];
			$xsldata = 
'<' . '?xml version="1.0" encoding="UTF-8"?' . '>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="//Disciplines/Discipline/@id">
		<xsl:attribute name="id">
			<xsl:choose>
' .
'				' . $xsltable .
'				<xsl:otherwise>
					<xsl:value-of select="." />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
		<xsl:attribute name="parentid">
			<xsl:choose>
' .
'				' . $xsltable2 .
'				<xsl:otherwise>
					<xsl:text></xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
		<xsl:attribute name="order">
			<xsl:choose>
' .
'				' . $xsltable3 .
'				<xsl:otherwise>
					<xsl:text></xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
	</xsl:template>
</xsl:stylesheet>			
';
			$xsl->loadXML($xsldata);
			$proc = new XSLTProcessor();
			$proc->registerPHPFunctions();
			$proc->importStylesheet($xsl);
			$xml2 = new DOMDocument();
			$xml2->loadXML($xml, LIBXML_NSCLEAN | LIBXML_COMPACT);
			$xml = $proc->transformToXml($xml2);
 */
			// cache entries
			// keep a backup of the old file, in order to revert it in case the transaction fails
			@exec("mv -f " . $this->vofile . ".old " . $this->vofile . ".old.bak");
			@exec("cp " . $this->vofile . " " . $this->vofile . ".old");
			$f = fopen($this->vofile,"w");
			fwrite($f,$xml);
			fclose($f);

			// update database
			if (($this->validateXMLCache($xml)) && (@md5_file($this->vofile) !== @md5_file($this->vofile . ".old"))) {
				error_log("Sync'ing EGI VOs...");
				db()->beginTransaction();
				$inTransaction = true;
				db()->query("ALTER TABLE vos DISABLE TRIGGER tr_vos_99_refresh_permissions");
				db()->query("ALTER TABLE vos DISABLE TRIGGER rtr__vos_cache_delta");
				db()->query("ALTER TABLE egiops.vo_contacts DISABLE TRIGGER tr_egiops_vo_contacts_99_refresh_permissions");
				db()->query("DELETE FROM egiops.vo_contacts");	// will be repopulated later on
				db()->query("DELETE FROM egiops.vos");
				$xmlobj = new \SimpleXMLElement($xml);
                $xvos = $xmlobj->xpath("//VoDump/IDCard");
                // add new VOs and update existing VOs
				foreach($xvos as $xvo) {
					$att = $xvo->attributes();
					$vd = strval($xvo->ValidationDate);
					if (substr($vd, 0, 4) === "0000") {
						$vd = null;
					}
					$xdiscs = $xvo->xpath("./Disciplines");
					$xdiscs = $this->parseDisc($xdiscs[0]);
					$discs = array();
					foreach ($xdiscs as $xdisc) {
						$discs[] = $xdisc["id"];
					}
					db()->query("INSERT INTO egiops.vos (name, scope, validated, description, homepage, enrollment, aup, domainname, disciplineid, alias, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, (?)::int[], ?, ?)", array(strtolower(trim($att["Name"])), trim($xvo->Scope), $vd, trim($xvo->Description), trim($xvo->HomepageUrl), trim($xvo->EnrollmentUrl), trim($xvo->AUP), trim($xvo->Discipline), php_to_pg_array($discs, false) ,trim($att["Alias"]), trim($att["Status"])));
				}
				db()->query("UPDATE vos SET
					scope = x.scope,
					validated = x.validated,
					description = x.description,
					homepage = x.homepage,
					enrollment = x.enrollment,
					aup = x.aup,
					domainid = COALESCE((SELECT id FROM domains WHERE LOWER(SUBSTRING(domains.name,1,10)) = LOWER(SUBSTRING(x.domainname,1,10))), 8),
					disciplineid = x.disciplineid,
					alias = x.alias,
					status = x.status
					FROM egiops.vos as x WHERE vos.name = x.name AND vos.sourceid = (SELECT id FROM vo_sources WHERE name = 'EGI Operations Portal')");
				db()->query("INSERT INTO vos (name,scope,validated,description,homepage,enrollment,aup,domainid,disciplineid,alias,status,sourceid) SELECT name,scope,validated,description,homepage,enrollment,aup,COALESCE((SELECT id FROM domains WHERE LOWER(SUBSTRING(name,1,10)) = LOWER(SUBSTRING(domainname,1,10))), 8),x.disciplineid,alias,status,(SELECT id FROM vo_sources WHERE name = 'EGI Operations Portal') AS sourceid FROM egiops.vos AS x WHERE NOT EXISTS (SELECT * FROM vos AS y WHERE y.name = x.name AND y.sourceid = (SELECT id FROM vo_sources WHERE name = 'EGI Operations Portal'))");
				db()->query("UPDATE vos SET deleted = TRUE WHERE sourceid = 1 AND LOWER(name) NOT IN (SELECT LOWER(name) FROM egiops.vos) AND NOT deleted");
				db()->query("UPDATE vos SET deleted = FALSE, deletedon = NULL WHERE sourceid = 1 AND LOWER(name) IN (SELECT LOWER(name) FROM egiops.vos) AND deleted");
				foreach($xvos as $xvo) {
					// sync vo / middleware relations. Remove existing and repopulate
					$att = $xvo->attributes();
                    db()->query('DELETE FROM vo_middlewares WHERE void = (SELECT id FROM vos WHERE name = ? AND sourceid = 1)', array(strtolower(trim($att["Name"]))));
                    if ( $xvo->Middlewares ) {
                        if ( strval($xvo->Middlewares->attributes()->gLite) == "1" ) {
                            db()->query('INSERT INTO vo_middlewares (void, middlewareid) VALUES ((SELECT id FROM vos WHERE name = ? AND sourceid = 1), 1)', array(strtolower(trim($att["Name"]))));
                        }
                        if ( strval($xvo->Middlewares->attributes()->ARC) == "1" ) {
                            db()->query('INSERT INTO vo_middlewares (void, middlewareid) VALUES ((SELECT id FROM vos WHERE name = ? AND sourceid = 1), 2)', array(strtolower(trim($att["Name"]))));
                        }
                        if ( strval($xvo->Middlewares->attributes()->UNICORE) == "1" ) {
                            db()->query('INSERT INTO vo_middlewares (void, middlewareid) VALUES ((SELECT id FROM vos WHERE name = ? AND sourceid = 1), 3)', array(strtolower(trim($att["Name"]))));
                        }
                        if ( strval($xvo->Middlewares->attributes()->GLOBUS) == "1" ) {
                            db()->query('INSERT INTO vo_middlewares (void, middlewareid) VALUES ((SELECT id FROM vos WHERE name = ? AND sourceid = 1), 4)', array(strtolower(trim($att["Name"]))));
                        }
					}
					// sync vo_resources.
					$sp_resources = "sync_egi_vos_resources" . (microtime(true) * 10000);
					db()->query("SAVEPOINT $sp_resources");
					$release_resources_savepoint = true;
					try {
						db()->query("DELETE FROM vo_resources WHERE void = (SELECT id FROM vos WHERE name = ? AND sourceid = 1)", array(strtolower(trim($att["Name"]))));
						if ( $xvo->Ressources ) {
							$xres = $xmlobj->xpath("//VoDump/IDCard[translate(@Name,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')='".strtoupper(trim($att["Name"]))."']/Ressources/*");
							foreach ($xres as $xr) {
								db()->query("INSERT INTO vo_resources (void, name, value) SELECT (SELECT id FROM vos WHERE name = ? AND sourceid = 1 AND NOT deleted), ?, ? WHERE NOT EXISTS (SELECT * FROM vo_resources WHERE void = (SELECT id FROM vos WHERE name = ? AND sourceid = 1 AND NOT deleted) AND name = ?)", array(strtolower(trim($att["Name"])), strval($xr->getName()), strval($xr), strtolower(trim($att["Name"])), strval($xr->getName())));
							}
						}
					} catch (Exception $e) {
						error_log("Error while syncing EGI vo resources for VO ". $att["Name"]);
						$release_resources_savepoint = false;
						db()->query("ROLLBACK TO SAVEPOINT $sp_resources");
						
					}
					if ($release_resources_savepoint) {
						db()->query("RELEASE SAVEPOINT $sp_resources");
					}
					// sync vo / contacts relations.
					$xcontacts = $xvo->xpath("./Contacts/Individuals/Contact");
					foreach( $xcontacts as $xcontact ) {
						db()->query("INSERT INTO egiops.vo_contacts (vo, name, role, email, dn) VALUES (?,?,?,?,?);", array(strtolower(trim($att["Name"])), str_replace("'", '’', trim($xcontact->Name)), trim($xcontact->Role), trim($xcontact->Email), str_replace("'", '’', trim($xcontact->DN))));
					}
					// sync vo / voms relations.
					db()->query('DELETE FROM vomses WHERE void = (SELECT id FROM vos WHERE name = ? AND sourceid = 1)', array(strtolower(trim($att["Name"]))));
					$void = db()->query("SELECT id FROM vos WHERE name = ?", array(strtolower(trim($att["Name"]))));
					$void = $void[0]->id;
					$xvomses = $xvo->xpath("./gLiteConf/VOMSServers/VOMS_Server");
					foreach( $xvomses as $xvoms ) {
						$voms = new \Application\Model\VOMS();
						$voms->void = $void;
						$voms->httpsPort = strval($xvoms->attributes()->HttpsPort);
						$voms->vomsesPort = strval($xvoms->attributes()->VomsesPort);
						$voms->isAdmin = strval($xvoms->attributes()->IsVomsAdminServer);
						$voms->memberListUrl = strval($xvoms->attributes()->MembersListUrl);
						$voms->hostname = strval($xvoms->hostname);
						$voms->save();
					}
				}
				db()->commit();
				db()->query("ALTER TABLE vos ENABLE TRIGGER tr_vos_99_refresh_permissions");
				db()->query("ALTER TABLE egiops.vo_contacts ENABLE TRIGGER tr_egiops_vo_contacts_99_refresh_permissions");
				db()->query("ALTER TABLE vos ENABLE TRIGGER rtr__vos_cache_delta");
				db()->query("SELECT rebuild_fulltext_index('vos');");
				db()->query("NOTIFY clean_cache;");
				db()->query("SELECT request_permissions_refresh()");
				error_log("EGI VOs sync'ed");
			} else {
				// no need to sync
				@exec("rm -f " . $this->vofile . ".old.bak");
				return false;
			}
		} catch (Exception $e) {
			$xml = false;
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
			};
			db()->query("ALTER TABLE vos ENABLE TRIGGER tr_vos_99_refresh_permissions");
			db()->query("ALTER TABLE egiops.vo_contacts ENABLE TRIGGER tr_egiops_vo_contacts_99_refresh_permissions");
			db()->query("ALTER TABLE vos ENABLE TRIGGER rtr__vos_cache_delta");
			db()->query("SELECT request_permissions_refresh()");
			// transaction failed. revert XML files to previous state
			@exec("mv -f " . $this->vofile . ".old " . $this->vofile);
			@exec("mv -f " . $this->vofile . ".old.bak " . $this->vofile . ".old");
			error_log('Error while syncing EGI VOs: '.$e);
			\ExternalDataNotification::sendNotification('VO::syncEGIVOs', $e->getMessage(), \ExternalDataNotification::MESSAGE_TYPE_ERROR);
		}
		@exec("rm -f " . $this->vofile . ".old.bak");
		return $xml;
	}

    private function fetchVOs()
    {
		if ( ! file_exists($this->vofile) ) {
			$xml = $this->syncVOs();
		} else {
			try {
				$xml = @file_get_contents($this->vofile);
				$this->validateXMLCache($xml);
			} catch (Exception $e) {
				error_log("[VoController::fetchVOs] " . $e->getMessage());
			}
		}
        return $xml;
	}

    private function getDomainId($name){
        if(trim($name)==="Multidisciplinary VOs"){
            $n = "Multidisciplinary";
        }else{
            $n = $name;
        }
        $ds = new \Application\Model\Domains();
        $ds->filter->name->equals($n);
        $id = $ds->items[0]->id;
        return $id;
    }
    private function getContactsOld($id)
    {
		$this->_helper->layout->disableLayout();
		$voname = $id;
		if ( $voname != null ) {
			$xml = new \SimpleXMLElement($this->xml);
			$volist = $xml->xpath('//VoDump/IDCard[@Name="'.$voname.'"]');
					$volist = $xml->xpath("//VoDump/IDCard[translate(@Name,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')='".strtoupper($voname)."']");
			if (count($volist)>0) {
				$clist = $volist[0]->xpath('./Contacts/Individuals/Contact');
				if (count($clist)>0) {
					$cs = array();
					foreach($clist as $c) {
						$r = array(
							'name' => $c->Name,
							'role' => $c->Role
						);
						$cs[] = $r;
					}
					return $cs;
				}
			}
		}
    }
	
	private function getContacts($id){
		$vocs = new \Application\Model\VOs();
		$vocs->filter->name->equals($id);
		if( count($vocs->items) > 0 ){
			$voc = $vocs->items[0];
			$contacts = $voc->getContacts();
			return $contacts;
		}
		return array();
	}
	
    public function detailsAction()
    {
		$this->view->canEdit = false;
        if ( $this->xml !== null ) {
            if ( GET_REQUEST_PARAM($this, "id") != null ) {
                $vos = new \Application\Model\VOs();
                $vos->filter->name->ilike(GET_REQUEST_PARAM($this, "id"));
				if( file_exists($_SERVER['APPLICATION_PATH'] . "/../../data/cache/aggvos.xml") ){
					$xml = new \SimpleXMLElement($_SERVER['APPLICATION_PATH'] . "/../../data/cache/aggvos.xml", 0, true);
				}else{
					$xml = new \SimpleXMLElement($this->xml);
				}
				$volist = $xml->xpath("//VoDump/IDCard[translate(@Name,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')='".strtoupper(GET_REQUEST_PARAM($this, "id"))."']");
				if (count($volist)>0) {
					$voentry = $volist[0];
					$vo = $this->populateVO($voentry);
					if ( $vos->count() > 0 ) {
						if ( isset($vos->items[0]) ) {
							$vo->id = $vos->items[0]->id; 
							$vo->guid = $vos->items[0]->guid;
							$vo->sourceid = $vos->items[0]->sourceid;
						} else {
							$vo = null;
						}
					} else {
						$vo->id = "";
						$vo->guid = "";
						$vo->sourceid = "";
					}
					if ( isset($vo) ) $vo->contacts = $this->getContacts(GET_REQUEST_PARAM($this, "id"));
					$this->view->entry = $vo;
					$this->view->relatedItems = array();
					$this->view->relatedItems = array_merge($this->view->relatedItems, $vo->applications);
					$this->view->relatedItems = array_merge($this->view->relatedItems, $vo->sites);
					$this->view->canEdit = \VoAdmin::canEditVOImageList($this->session->userid, $vo);
				}
            }
            $this->view->session = $this->session;
            $this->view->dialogCount = GET_REQUEST_PARAM($this, 'dc');
			
        } else {
            $this->printError();
		}
		return DISABLE_LAYOUT($this);
    }

    public function listAction(){
        $this->_helper->layout->disableLayout();
    }

    public function indexAction()
    {
		return DISABLE_LAYOUT($this);
    }

	public function alphanumericreportAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('Content-type: text/xml');
		$r = getAlphnumericReport("vos", $_GET["flt"]);
		$len = count($r);
		echo "<report count='".$len."'>";
		for( $i = 0; $i < $len; $i+=1 ) {
			echo "<item count='" . $r[$i]["cnt"] . "' value='" . $r[$i]["typechar"] . "' />";
		}
		echo "</report>";
	}

	public function refreshvousersAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if ( localRequest() ) {
			$this->syncVOMembers();
		} else {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
		}
	}

	public function syncVOMembers() {
		db()->query("INSERT INTO config (var, data) SELECT 'egi_vo_members_synced', NULL WHERE NOT EXISTS (SELECT * FROM config WHERE var = 'egi_vo_members_synced')");
		db()->query("INSERT INTO config (var, data) SELECT 'ebi_vo_members_synced', NULL WHERE NOT EXISTS (SELECT * FROM config WHERE var = 'ebi_vo_members_synced')");
		$this->syncEGIVOMembers();
		$this->syncEBIVOMembers();
	}

	public function syncEBIVOMembers() {
		$rs = db()->query("SELECT id, name, enabled, members_url FROM vo_sources WHERE name = 'EBI-Perun'", array())->toArray();
		$enabled = false;
		$uri = null;
		if (count($rs) > 0) {
			$rs = $rs[0];
			if (filter_var($rs['enabled'], FILTER_VALIDATE_BOOLEAN) === true) $enabled = true;
			$uri = $rs['members_url'];
		}
		if (! $enabled) {
			error_log("EBI-Perun VO source is disabled; will not sync VO members");
			\ExternalDataNotification::sendNotification('VO::syncEBIVOMembers', "EBI-Perun VO source is disabled; will not sync VO members", \ExternalDataNotification::MESSAGE_TYPE_ERROR);
			return false;
		}
		$inTransaction = false;
		try {
			if ($_SERVER['APPLICATION_ENV]'] == "production") {
				$ch = curl_init();
				if (is_null($uri)) $uri = "https://perun.cesnet.cz/external/appdb/users";
				curl_setopt($ch, CURLOPT_URL, $uri);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, 181, 1 | 2);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSLCERT, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/usercert.pem');
				curl_setopt($ch, CURLOPT_SSLKEY, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/userkey.pem');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
				$headers = apache_request_headers();
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$xml = curl_exec($ch);

				if ( $xml === false ) {
					$err = var_export(curl_error($ch), true);
					error_log("error in syncEBIVOMembers: " . $err);
					\ExternalDataNotification::sendNotification('VO::syncEBIVOMembers', 'Could not sync VO members from EBI-Perun. Error was:\n\n' . $err);
					return;
				} else {
					$xml = "<results>$xml</results>";
				}	
				@curl_close($ch);
				@exec("rm ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivo_users.xml.old");
				@exec("mv " . $_SERVER['APPLICATION_PATH'] . "/../../data/cache/egivo_users.xml ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivo_users.xml.old");
				$f = fopen($_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivo_users.xml","w");
				fwrite($f, $xml);
				fclose($f);
			}
			if (@md5_file($_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivo_users.xml") !== @md5_file($_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivo_users.xml.old")) {
				$xmldata = file_get_contents($_SERVER['APPLICATION_PATH'] . "/../../data/cache/ebivo_users.xml");
				if (mb_detect_encoding($xmldata, "UTF-8", true) === false) {
					$xmldata = recode_string("iso8859-1..utf8", $xmldata);
				}
				$xml = new \SimpleXMLElement($xmldata);
				$rows = $xml->xpath("//result/row");
				if (count($rows) > 0) {
					error_log("Sync'ing EBI VO members...");
					db()->beginTransaction();
					$inTransaction = true;
					db()->query("ALTER TABLE perun.vo_members DISABLE TRIGGER tr_perun_vo_members_99_refresh_permissions;");
					db()->query("DELETE FROM perun.vo_members;");
					foreach($rows as $row) {
						$lastup = trim($row->last_update);
						$firstup = trim($row->first_update);
						if ($lastup == "") { $lastup = null; }
						if ($firstup == "") { $firstup = null; }
						$xdns = $row->dn;
						$dns = array();
						$cas = array();
						foreach ($xdns as $xdn) {
							$dnatt = $xdn->attributes();
							$dns[] = str_replace("'", '’', trim(strval($xdn)));
							$cas[] = trim(strval($dnatt["ca"]));
						}
						$dns = php_to_pg_array($dns, false);
						$cas = php_to_pg_array($cas, false);
						if ($dns === '{}') $dns = null;
						if ($cas === '{}') $cas = null;
						error_log("dn: " . var_export($dns, true));
						error_log("ca: " . var_export($cas, true));
						$xeppns = $row->eppn;
						$eppns = array();
						foreach($xeppns as $xeppn) {
							$eppns[] = strval($xeppn);
						}
						$eppns = php_to_pg_array($eppns, false);
						if ($eppns === '{}') $eppns = null;
						db()->query("INSERT INTO perun.vo_members (uservo, certdn, ca, vo, last_update, first_update, sso, eppn) VALUES (?,(?)::text[],(?)::text[],?,?,?,?,(?)::text[]);", array(str_replace("'", '’', trim($row->uservo)), $dns, $cas, trim($row->vo), $lastup, $firstup, trim($row->sso), $eppns));
					}
					db()->commit();
					db()->query("ALTER TABLE perun.vo_members ENABLE TRIGGER tr_perun_vo_members_99_refresh_permissions;");
					db()->query("SELECT request_permissions_refresh();");
					db()->query("UPDATE config SET data = NOW()::text WHERE var = 'ebi_vo_members_synced'");
					error_log("EBI VO members sync'ed");
				} else {
					\ExternalDataNotification::sendNotification('VO::syncEBIVOMembers', 'Could not sync VO members from EBI-Perun. Probably got currupt or empty data');
				}
			} else {
				error_log("Sync EBI VO members: nothing to do (MD5 unchanged)");
				db()->query("UPDATE config SET data = NOW()::text WHERE var = 'ebi_vo_members_synced'");
			}
		} catch (Exception $e) {
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
			}
			db()->query("ALTER TABLE perun.vo_members ENABLE TRIGGER tr_perun_vo_members_99_refresh_permissions;");
			db()->query("SELECT request_permissions_refresh();");
			error_log("error in syncEBIVOMembers: $e");
			\ExternalDataNotification::sendNotification('VO::syncEBIVOMembers', 'Could not sync VO members from EBI-Perun. Error was:\n\n' . $e->getMessage());
		}
	}

	public function syncEGIVOMembers() {
		if ($this->gridops_is_down()) {
			error_log("EGI Operations portal is in downtime. EGI VO members sync aborted");
			return;
		}
		$rs = db()->query("SELECT id, name, enabled, members_url FROM vo_sources WHERE name = 'EGI Operations Portal'", array())->toArray();
		$enabled = false;
		$uri = null;
		if (count($rs) > 0) {
			$rs = $rs[0];
			if (filter_var($rs['enabled'], FILTER_VALIDATE_BOOLEAN) === true) $enabled = true;
		}
		if (! $enabled) {
			error_log("EGI Operations Portal VO source is disabled; will not sync VO members");
			\ExternalDataNotification::sendNotification('VO::syncEGIVOMembers', "EGI Operations Portal VO source is disabled; will not sync VO members", \ExternalDataNotification::MESSAGE_TYPE_ERROR);
			return false;
		}
		$inTransaction = false;
		//FIXME: $mode = Zend_Registry::get("vouser_sync");
		if (is_array($mode) && isset($mode["mode"])) {
			$mode = $mode["mode"];
		} else {
			$mode = "api";
		}
		switch ($mode) {
		case "api":
		case "zip":
			break;
		default:
			$mode = "api";
			break;
		}
		error_log("EGI VO user sync mode: $mode");
		try {
			$ch = curl_init();
			if ($mode == "api") {
				$uri = "https://operations-portal.egi.eu/vo/downloadVoUsers";
			} elseif ($mode == "zip") {
				$uri = "http://cclavoisier01.in2p3.fr:8080/lavoisier/OPSCORE_vo_users_raw?accept=zip";
			} else {
				return;
			}
			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			if ($mode == "api") {
				curl_setopt($ch, 181, 1 | 2);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSLCERT, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/usercert.pem');
				curl_setopt($ch, CURLOPT_SSLKEY, $_SERVER['APPLICATION_PATH'] . '/../../bin/sec/userkey.pem');
			} elseif ($mode == "zip") {
				curl_setopt($ch, 181, 1);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
			$headers = apache_request_headers();
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$xml = curl_exec($ch);
			
			if ( $xml === false ) {
				$err = var_export(curl_error($ch), true);
				error_log("error in syncEGIVOMembers: " . $err);
				\ExternalDataNotification::sendNotification('VO::syncEGIVOMembers', "Could not sync VO members from EGI operations portal. Error was:\n\n" . $err); 
				return;
			}
			@curl_close($ch);
			if ($mode == "api") {
				@exec("rm ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz.old");
				@exec("rm ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml");
				@exec("cp " . $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz.old");
				$f = fopen($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz","w");
			} elseif ($mode == "zip") {
				@exec("rm ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.old");
				@exec("mv " . $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.old");
				$f = fopen($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.zip","w");
			} 
			fwrite($f, $xml);
			fclose($f);
			if ($mode == "zip") {
				@exec("unzip -p " . $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.zip data.xml > " . $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml");
				@exec("rm ". $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.zip");
			}
			if (
				(($mode == "api") && (@md5_file($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz") !== @md5_file($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz.old")))
				|| 
				(($mode == "zip") && (@md5_file($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml") !== @md5_file($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.old")))
			) {
				if ($mode == "api") {
					exec("gunzip " . $_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml.gz");
				}
				$xml = new \SimpleXMLElement(file_get_contents($_SERVER['APPLICATION_PATH'] . "/../../data/cache/vo_users.xml"));
				$rows = $xml->xpath("//result/row");
				if (count($rows) > 0) {
					error_log("Sync'ing VO members...");
					db()->beginTransaction();
					$inTransaction = true;
					db()->query("ALTER TABLE egiops.vo_members DISABLE TRIGGER tr_egiops_vo_members_99_refresh_permissions;");
					db()->query("DELETE FROM egiops.vo_members;");
					foreach($rows as $row) {
						$lastup = trim($row->LAST_UPDATE);
						$firstup = trim($row->FIRST_UPDATE);
						if ($lastup == "") { $lastup = null; }
						if ($firstup == "") { $firstup = null; }
						db()->query("INSERT INTO egiops.vo_members (uservo, certdn, ca, vo, last_update, first_update) VALUES (?,?,?,?,?,?);", array(str_replace("'", '’', trim($row->USERVO)), str_replace("'", '’', trim($row->CERTDN)), trim($row->CA), trim($row->VO), $lastup, $firstup));
					}
					db()->commit();
					db()->query("ALTER TABLE egiops.vo_members ENABLE TRIGGER tr_egiops_vo_members_99_refresh_permissions;");
					db()->query("SELECT request_permissions_refresh();");
					db()->query("UPDATE config SET data = NOW()::text WHERE var = 'egi_vo_members_synced'");
					error_log("VO members sync'ed");
				} else {
					\ExternalDataNotification::sendNotification('VO::syncEGIVOMembers', 'Could not sync VO members from EGI operations portal. Probably got currupt or empty data');
				}
			} else {
				error_log("Sync EGI VO members: nothing to do (MD5 unchanged)");
				db()->query("UPDATE config SET data = NOW()::text WHERE var = 'egi_vo_members_synced'");
			}
		} catch (Exception $e) {
			if ($inTransaction) {
				$db = db();
				@$db->rollBack();
			}
			db()->query("ALTER TABLE egiops.vo_members ENABLE TRIGGER tr_egiops_vo_members_99_refresh_permissions;");
			db()->query("SELECT request_permissions_refresh();");
			error_log("error in syncVOMembers: $e");
			\ExternalDataNotification::sendNotification('VO::syncEGIVOMembers', 'Could not sync VO members from EGI operations portal. Error was:\n\n' . $e->getMessage());
		}
	}
	
	public function imagelistAction(){
		if ($this->session->isLocked()) {
			$this->session->unLock();
		}
		session_write_close();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		if( $this->session->userid === null ) {
			$this->getResponse()->clearAllHeaders();
			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
			$this->getResponse()->setHeader("Status","403 Forbidden");
			return;
		}
		$action = ( ( isset($_POST["action"]) )?trim($_POST["action"]):"" );
		$vappliance = ( ( isset($_POST["vappid"]) && is_numeric($_POST["vappid"]))?intval($_POST["vappid"]):-1 );
		$vo = ( ( isset($_POST["void"]) && is_numeric($_POST["void"]))?intval($_POST["void"]):-1 );
		
		header('Content-type: text/xml');
		if( $action === "publish" || $action === "revertchanges" ){
			$vappliance = null;
		}
		$result = \VoAdmin::imageAction($action, $this->session->userid, $vo, $vappliance);
		if( is_string($result) === true ){
			echo "<result success='false' error='" . htmlentities($result) . "' ></result>";
		}else if( $result === true ){
			echo "<result success='true' ></result>";
		}else{
			echo "<result success='false' error='Could not " . $action . " vo image list' ></result>";
		}
	}
	
	public function voimageAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$format =GET_REQUEST_PARAM($this, "format");
		$guid = trim(GET_REQUEST_PARAM($this, "guid"));
		$accesstoken = trim(GET_REQUEST_PARAM($this, "accesstoken"));
		$strict = ((isset($_GET["strict"]))?true:false);
		
		if( $format === null || $format === "json"){
			header('Content-type: application/json');
		}else if( $format === "xml" ){
			header('Content-type: application/xml');
		}
		
		if( $guid !== ""){
			$imageid = null;
			if( strpos($guid, ":") !== false ){
				$tmp = explode(":",$guid);
				if( count($tmp) > 1 ){
					$guid = $tmp[0];
					$imageid = $tmp[1];
				}
			}
			if( $imageid !== null ){
				$result = \VoAdmin::getImageInfoById($imageid,$guid,$strict);
			}else{
				$result = \VoAdmin::getImageInfoByIdentifier($guid);
			}
			$canaccessvadata = false;
			
			if( $result !== null ){
				$result["isprivateimage"] = false;
				$result["canaccessprivate"] = true;
				$vapp = $result["va"];
				if( $vapp->imglstprivate ){
					$result["isprivateimage"] = true;
					$result["canaccessprivate"] = false;
					
					$vapp = $result["va"];
					$app = $vapp->getApplication();
					if( $privs !== null ){
						$canaccessvadata = $privs->canAccessVAPrivateData($app->guid);
					}
					
					$result["canaccessprivate"] = $canaccessvadata;
				}
			}
			
			if( $result !== null && isset($result['image']) ){
				$im = $result['image'];
				$result['sites'] = \VMCaster::getSitesByVMI($im->guid, $im->id);
			}
			
			if( $result !== null && $format == null ){ //UI call
				$result["result"] = "success";
				$va = $result["va"];
				$app = $va->getApplication();
				$version = $result["version"];
				$image = $result["image"];
				$vo = $result["vo"];
				$voimage = $result["voimage"];
				$voimagelist = $result["voimagelist"];
				
				$result["app"] = array("id"=>$app->id,"name"=>$app->name,"cname"=>$app->cname);
				$result["va"] = array("id"=>$va->id);
				$result["version"] = array("id"=>$version->id,"version"=>$version->version,"published"=>$version->published,"archived"=>$version->archived,"enabled"=>$version->enabled);
				$result["image"] = array("id"=>$image->id,"identifier"=>$voimage->guid, "baseidentifier"=>$image->guid);
				$result["vo"] = array("id"=>$vo->id, "name"=> $vo->name, "domain"=>$vo->domain->name);
				$result["voimagelist"] = array("id"=>$voimagelist->id, "state"=>$voimagelist->state);
				$result["voimage"] = array("id"=>$voimage->id, "state"=>$voimage->state);
				
				echo json_encode($result,JSON_HEX_TAG | JSON_NUMERIC_CHECK );
				return;
			}else if( $format !== null) {
				if( $result !== null ){
					$result = \VMCaster::convertImage($result, $format);
				}
				if( $result !== null ){
					echo $result;
				}else{
					header('HTTP/1.0 404 Not Found');
					header("Status: 404 Not Found");
				}
				return;
			}
		}
		echo json_encode(array("result"=>"error", "message"=>"Image not found"));
	}


////OBSOLETE	
//	private function getVAProvidersArray($inprodOnly = true) {
//		$vaps = new \Application\Model\VaProviders();
//		if ($inprodOnly === true) {
//			$vaps->filter->in_production->equals(true);
//		}
//		$ret = array();
//		foreach ($vaps->items as $vap) {
//			$url=trim(substr($vap->url,0,(strpos($vap->url,"?") == true ? strpos($vap->url,"?") : strlen($vap->url))),'/');
//			preg_match('/(?P<hostname>[a-zA-Z0-9.\-_]+):(?P<port>\d+)/', $url, $matches);
//			if($matches){
//				$pat=$matches['hostname'].":".$matches['port'];
//				$url = substr($url,0,(strpos($url,$pat) + strlen($pat)));
//			}
//			$ret[] = array("id" => $vap->id, "name" => $vap->sitename, "url" => $url, "serviceid" => $vap->serviceid);
////          $ret[] = array("id" => $vap->id, "name" => $vap->sitename, "url" => trim(substr($vap->url,0,(strpos($vap->url,"?") == true ? strpos($vap->url,"?") : strlen($vap->url))),'/'), "serviceid" => $vap->serviceid);
//		}
//		return $ret;
//	}
//
//	private function getTopBDIIData ($basedn, $filter, $attrs) {
//		$top="topbdii.appdb.marie.hellasgrid.gr:2170";
//		$cnx = ldap_connect($top);
//		ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
//		ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
//		$info = array();
//		$search = @ldap_search($cnx,$basedn,$filter, $attrs);
//		if ($search) {
//			$info = ldap_get_entries($cnx,$search);
//		}
//		return $info;
//	}
//
//	public function syncvaproviderimagesAction() {
//		if ( localRequest() ) {
//			$this->_helper->layout->disableLayout();
//			$this->_helper->viewRenderer->setNoRender();
//			error_log(gmdate("Y-m-d H:i:s", time()) . ": Sync VA Provider Images START");
//			$filter = '(&(GLUE2ApplicationEnvironmentRepository=*/appdb.egi.eu/*))';
//			$attrs = array(
//				'dn',
//				'GLUE2ApplicationEnvironmentID',
//				'GLUE2ApplicationEnvironmentAppName',
//				'GLUE2ApplicationEnvironmentDescription',
//				'GLUE2ApplicationEnvironmentComputingManagerForeignKey',
//				'GLUE2EntityName',
//				'GLUE2ApplicationEnvironmentAppVersion',
//				'GLUE2ApplicationEnvironmentRepository'
//			);
//
//			try {
//				db()->beginTransaction();
//				$prod_sites = $this->getVAProvidersArray();
//				db()->query("TRUNCATE TABLE va_provider_images");
//				foreach ($prod_sites as $site) {
//					$basedn = 'GLUE2GroupID=cloud,GLUE2DomainID=' . $site["name"] . ',GLUE2GroupID=grid,o=glue';
//					if(trim($site["serviceid"]) != "") {
//						$basedn = 'GLUE2ServiceID=' . $site["serviceid"] . ',' . $basedn;
//					} else {
//						continue; // if there is no service id, no images should be queried
//					}
//					$result = $this->getTopBDIIData($basedn, $filter, $attrs);
//					if (!empty($result)) {
//						if (isset($result["count"])) {
//							if ($result["count"] <= 0) {
//								//throw new Exception("Number of results returned by top-BDII is zero. Aborting operation.");
//								error_log("Warning: Number of image results returned by top-BDII is zero for " . $site["name"]);
//							}
//							for ($i = 0; $i < $result["count"]; $i++) {
//								$mpURI = $result[$i]["glue2applicationenvironmentrepository"][0];
//								$imageID = $result[$i]["glue2entityname"][0];
//
//								# parse mpURI and find type
//								$start = strpos($mpURI,"/store/") + strlen("/store/");
//								$len = strpos($mpURI,"/image/") - $start;
//								$type = substr($mpURI,$start,$len);
//
//								# parse mpURI and find instanceID
//								$mpURI_tmp = parse_url($mpURI,PHP_URL_PATH);
//								$params = substr($mpURI_tmp, strrpos($mpURI_tmp,'image/')+5);
//								if( strlen($params) > 1 ){
//									$params = explode('/',$params);
//									$params = explode(':',$params[1]);
//
//									if( count($params) > 1 ){
//										if( is_numeric($params[1])){
//											$instanceID = intval($params[1]);
//										}
//
//									}
//								}
//
//								$vowide_instanceID = null;
//
//								if ($type == "vo") {
//									$vowide_instanceID = $instanceID;
//									$instanceID = db()->query("SELECT vapplists.vmiinstanceid FROM vowide_image_list_images INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid WHERE vowide_image_list_images.id = ?", array($instanceID))->toArray();								
//									if (count($instanceID) > 0) {
//										if (count($instanceID[0]) > 0) {
//											$instanceID = $instanceID[0][0];
//										} else {
//											$instanceID = null;
//										}
//									} else {
//										$instanceID = null;
//									}
//								}
//								$sp_vap_img = "sync_va_provider_images" . (microtime(true) * 10000);
//								db()->query("SAVEPOINT $sp_vap_img");
//								$release_vap_img = true;
//								try {
//									db()->query("INSERT INTO va_provider_images (va_provider_id, vmiinstanceid, content_type, va_provider_image_id, mp_uri, vowide_vmiinstanceid) VALUES (?, ?, ?, ?, ?, ?)", array($site["id"], $instanceID, $type, $imageID, $mpURI, $vowide_instanceID));
//								} catch (Exception $e) {
//									error_log("ERROR in 'INSERT INTO va_provider_images (va_provider_id, vmiinstanceid, content_type, va_provider_image_id, mp_uri, vowide_vmiinstanceid)' -- entry ignored");
//									error_log("VALUES: " . 
//										"'" . var_export($site["id"], true) . "', " .  
//										"'" . var_export($instanceID, true) . "', " .
//										"'" . var_export($type, true) . "', " .
//										"'" . var_export($imageID, true) . "', " .
//										"'" . var_export($mpURI, true) . "', " .
//										"'" . var_export($vowide_instanceID, true) . "', "
//									);
//									$release_vap_img = false;
//									db()->query("ROLLBACK TO SAVEPOINT $sp_vap_img");
//								}
//								if ($release_vap_img) {
//									db()->query("RELEASE SAVEPOINT $sp_vap_img");
//								}
//							}
//						}
//					}
//				}
//				error_log(gmdate("Y-m-d H:i:s", time()) . ": Sync VA Provider Images DONE [1/2]. Will refresh related materialized views");
//				error_log(gmdate("Y-m-d H:i:s", time()) . ": Sync VA Provider Images: Refreshing site_services_xml...");
//				db()->query("REFRESH MATERIALIZED VIEW site_services_xml;");
//				error_log(gmdate("Y-m-d H:i:s", time()) . ": Sync VA Provider Images: Refreshing site_service_images_xml...");
//				db()->query("REFRESH MATERIALIZED VIEW site_service_images_xml;");
//				error_log(gmdate("Y-m-d H:i:s", time()) . ": Sync VA Provider Images DONE [2/2]");
//				db()->commit();
//				db()->query("DELETE FROM cache.filtercache WHERE m_from LIKE '%FROM sites%'");
//				sleep(2); // give the commit some time to settle before making next two calls
//				$this->makeVAprovidersCache();
//				if ( strtolower($_SERVER["SERVER_NAME"]) == "appdb.egi.eu" ) {
//					web_get_contents("https://dashboard.appdb.egi.eu/services/appdb/sync/cloud");
//				}
//			} catch (Exception $e) {
//				error_log($e->getMessage());
//				error_log(gmdate("Y-m-d H:i:s", time()) . ": Sync VA Provider Images FAILED");
//				db()->rollBack();
//			}
//		} else {
//			$this->getResponse()->clearAllHeaders();
//			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
//			$this->getResponse()->setHeader("Status","403 Forbidden");
//		}
//	}
//
//
//	public function syncvaprovidertemplatesAction() {
//		if ( localRequest() ) {
//			$this->_helper->layout->disableLayout();
//			$this->_helper->viewRenderer->setNoRender();
//			error_log("Sync VA Provider Templates START");
//			$filter = '(objectClass=GLUE2ExecutionEnvironment)';
//			$attrs = array(
//				'GLUE2EntityName',
//				'GLUE2ExecutionEnvironmentMainMemorySize',
//				'GLUE2ExecutionEnvironmentLogicalCPUs',
//				'GLUE2ExecutionEnvironmentCPUMultiplicity',
//				'GLUE2ResourceManagerForeignKey',
//				'GLUE2ExecutionEnvironmentOSFamily',
//				'GLUE2ExecutionEnvironmentConnectivityIn',
//				'GLUE2ExecutionEnvironmentConnectivityOut',
//				'GLUE2ExecutionEnvironmentCPUModel',
//				'GLUE2ResourceID',
//				'GLUE2ExecutionEnvironmentPhysicalCPUs',
//				'GLUE2ExecutionEnvironmentComputingManagerForeignKey',
//				'GLUE2EntityOtherInfo',
//			);
//
//			$prod_sites = $this->getVAProvidersArray();
//			try {
//				db()->beginTransaction();
//				db()->query("TRUNCATE TABLE va_provider_templates");
//				foreach($prod_sites as $site) {
//					$basedn = 'GLUE2GroupID=cloud,GLUE2DomainID=' . $site["name"] . ',GLUE2GroupID=grid,o=glue';
//					if(trim($site["serviceid"]) != "") {
//						$basedn = 'GLUE2ServiceID=' . $site["serviceid"] . ',' . $basedn;
//					} else {
//						continue; // if there is no service id, no templates should be queried
//					}
//					$result = $this->getTopBDIIData($basedn, $filter, $attrs);
//					$disc_size = null;
//					if (isset($result["count"])) {
//						if ($result["count"] <= 0) {
//							//throw new Exception("Number of results returned by top-BDII is zero. Aborting operation.");
//							error_log("Number of template results returned by top-BDII is zero for " . $site["name"]);
//						}
//						for ($i = 0; $i < $result["count"]; $i++) {
//							if (array_key_exists("glue2entityotherinfo", $result[$i])) {
//								if (trim($result[$i]["glue2entityotherinfo"][0]) != "") {
//									$pregm = array();
//									preg_match('/\bdisk=([0-9]+)\b/', trim($result[$i]["glue2entityotherinfo"][0]), $pregm);
//									if (count($pregm)>=2) {
//										if (is_numeric($pregm[1])) {
//											$disc_size = intval($pregm[1]);
//										}
//									}
//								}
//							}
//							$sp_vap_tmpl = "sync_va_provider_templates" . (microtime(true) * 10000);
//							db()->query("SAVEPOINT $sp_vap_tmpl");
//							$release_vap_tmpl = true;
//							try {
//								db()->query("INSERT INTO va_provider_templates (va_provider_id, resource_name, memsize, logical_cpus, physical_cpus, cpu_multiplicity, resource_manager, computing_manager, os_family, connectivity_in, connectivity_out, cpu_model, disc_size, resource_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(
//									$site["id"],
//									$result[$i]["glue2entityname"][0],
//									$result[$i]["glue2executionenvironmentmainmemorysize"][0],
//									$result[$i]["glue2executionenvironmentlogicalcpus"][0],
//									$result[$i]["glue2executionenvironmentphysicalcpus"][0],
//									$result[$i]["glue2executionenvironmentcpumultiplicity"][0],
//									$result[$i]["glue2resourcemanagerforeignkey"][0],
//									$result[$i]["glue2executionenvironmentcomputingmanagerforeignkey"][0],
//									$result[$i]["glue2executionenvironmentosfamily"][0],
//									$result[$i]["glue2executionenvironmentconnectivityin"][0],
//									$result[$i]["glue2executionenvironmentconnectivityout"][0],
//									$result[$i]["glue2executionenvironmentcpumodel"][0],
//									$disc_size,
//									$result[$i]["glue2resourceid"][0]
//								));
//							} catch (Exception $e) {
//								error_log("ERROR in 'INSERT INTO va_provider_templates (va_provider_id, resource_name, memsize, logical_cpus, physical_cpus, cpu_multiplicity, resource_manager, computing_manager, os_family, connectivity_in, connectivity_out, cpu_model, disc_size, resource_id)' -- entry ignored");
//								error_log("VALUES: " . 
//									"'" . var_export($site["id"], true) . "', " .
//									"'" . var_export($result[$i]["glue2entityname"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentmainmemorysize"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentlogicalcpus"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentphysicalcpus"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentcpumultiplicity"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2resourcemanagerforeignkey"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentcomputingmanagerforeignkey"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentosfamily"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentconnectivityin"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentconnectivityout"][0], true) . "', " .
//									"'" . var_export($result[$i]["glue2executionenvironmentcpumodel"][0], true) . "', " .
//									"'" . var_export($disc_size, true) . "', " .
//									"'" . var_export($result[$i]["glue2resourceid"][0], true) . "'"
//								);
//								$release_vap_tmpl = false;
//								db()->query("ROLLBACK TO SAVEPOINT $sp_vap_tmpl");
//							}
//							if ($release_vap_tmpl) {
//								db()->query("RELEASE SAVEPOINT $sp_vap_tmpl");
//							}
//						}
//					}
//				}	
//				error_log("Sync VA Provider Templates DONE");
//				db()->commit();
//			} catch (Exception $e) {
//				error_log($e->getMessage());
//				error_log("Sync VA Provider Templates FAILED");
//				db()->rollBack();
//			}
//		} else {
//			$this->getResponse()->clearAllHeaders();
//			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
//			$this->getResponse()->setHeader("Status","403 Forbidden");
//		}
//	}
//
//	public function syncvaproviderendpointsAction() {
//		if ( localRequest() ) {
//			$this->_helper->layout->disableLayout();
//			$this->_helper->viewRenderer->setNoRender();
//			error_log("Sync VA Provider Endpoints START");
//			$attrs = array(
//				'GLUE2EndpointURL',
//				'GLUE2EndpointImplementationName',
//				'GLUE2EndpointImplementor',
//				'GLUE2ComputingEndpointComputingServiceForeignKey',
//				'GLUE2EntityOtherInfo',
//				'GLUE2EndpointCapability',
//				'GLUE2EndpointImplementationVersion',
//				'GLUE2EndpointID',
//				'GLUE2EndpointTechnology',
//				'GLUE2EndpointHealthState',
//				'GLUE2EndpointServingState',
//				'GLUE2EndpointServiceForeignKey',
//				'GLUE2ComputingEndpointComputingServiceForeignKey',
//				'GLUE2EndpointImplementor'
//			);
//
//			$prod_sites = $this->getVAProvidersArray(false);
//			try {
//				db()->beginTransaction();
//				db()->query("TRUNCATE TABLE va_provider_endpoints");
//				$k=1;
//				foreach($prod_sites as $site) {
//					
//					$basedn='GLUE2GroupID=cloud,GLUE2DomainID='.$site["name"].',GLUE2GroupID=grid,o=glue';
//					$filter = '(&(objectClass=GLUE2Endpoint)(|(GLUE2EndpointInterfaceName=OCCI)))';
//					if ($site["url"] != '') {
//						$filter = '(&(objectClass=GLUE2Endpoint)(|(GLUE2EndpointID='.$site["url"].'*))(|(GLUE2EndpointInterfaceName=OCCI)))';
//					}
//					$result = $this->getTopBDIIData($basedn, $filter, $attrs);
//					if(isset($result["count"])){
//						if ($result["count"] <= 0) {
//							//throw new Exception("Number of results returned by top-BDII is zero. Aborting operation.");
//							error_log("Warning: Number of endpoint results returned by top-BDII is zero for " . $site["name"]);
//						}
//						for($i=0; $i<$result["count"];$i++) {
//							$sp_vap_endp = "sync_va_provider_endpoints" . (microtime(true) * 10000);
//							db()->query("SAVEPOINT $sp_vap_endp");
//							$release_vap_endp = true;
//							try {								
//								db()->query("INSERT INTO va_provider_endpoints (va_provider_id, endpoint_url, deployment_type) VALUES (?, ?, ?)", array(
//									$site["id"],
//									$result[$i]["glue2endpointurl"][0],
//									$result[$i]["glue2endpointimplementor"][0]
//								));
//								error_log($k.". Endpoint processed SITE: ".$site["name"]." ID:".$site["id"]." VALUE: ".$result[$i]["glue2endpointurl"][0]);
//								if(trim($result[$i]["glue2computingendpointcomputingserviceforeignkey"][0]) != ""){
//									db()->query("UPDATE gocdb.va_providers SET serviceid='".trim($result[$i]["glue2computingendpointcomputingserviceforeignkey"][0])."' WHERE gocdb.va_providers.pkey='".$site["id"]."'");
//								}
//							} catch (Exception $e) {
//								error_log("ERROR in 'INSERT INTO va_provider_endpoints(va_provider_id, vmiinstanceid, content_type, va_provider_image_id, mp_uri, vowide_vmiinstanceid)' -- entry ignored");
//								$release_vap_endp = false;
//								db()->query("ROLLBACK TO SAVEPOINT $sp_vap_endp");
//							}
//							if ($release_vap_endp) {
//								db()->query("RELEASE SAVEPOINT $sp_vap_endp");
//							}
//						}
//					}
//				$k++;
//				}
//				db()->query("REFRESH MATERIALIZED VIEW CONCURRENTLY va_providers;");
//				db()->query("SELECT request_permissions_refresh();");
//				db()->commit();
//				error_log("Sync VA Provider Endpoints DONE");
//			} catch (Exception $e) {
//				error_log($e->getMessage());
//				error_log("Sync VA Provider Endpoints FAILED");
//				db()->rollBack();
//			}
//		} else {
//			$this->getResponse()->clearAllHeaders();
//			$this->getResponse()->setRawHeader("HTTP/1.0 403 Forbidden");
//			$this->getResponse()->setHeader("Status","403 Forbidden");
//		}
//	}
	
	public function dispatchobsoleteimagelistAction(){
	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$dispatch = ( ( isset( $_GET["dispatch"] )  )?( $_GET["dispatch"] ) : "false" );
		$islocal = localRequest();
		$isAdmin = userIsAdminOrManager($this->session->userid);
		
		if(strtolower(trim($dispatch)) === "true"){
			$dispatch = true;
		}else{
			$dispatch = false;
		}
		
		if( ($dispatch === true && $islocal === false) ||
			($dispatch === false && $isAdmin === false )
		){
			header('HTTP/1.0 404 Not Found');
			header("Status: 404 Not Found");
			return;
		}
		
		if( $dispatch === false ){
			$res = \VoAdminNotifications::createVOObsoleteNotifications();
			echo "<h2>VO Obsolete Images Notifications:</h2>";
			foreach ($res as $r){
				echo "<div class='notification' style='border:1px solid #aaa;background-color:#f8f8f8;margin: 5px;margin-bottom:20px;padding:10px;'>";
				echo "<div class='recipient'>recipients:  <pre style='display:inline;white-space:pre-wrap;color: #333;'>" . implode(", ",$r["recipients"]) . "</pre></div>";
				echo "<div class='subject'>subject:    <pre style='display:inline;'>" . $r["subject"] . "</pre></div>";
				echo "<div style='padding:5px;border:1px solid #bbb;background-color:#fefefe;margin-top:5px;padding:3px;'><pre style='padding:5px;'>" . htmlentities($r["message"]) . "</pre></div>";
				echo "</div>";
			}
		}else{
			\VoAdminNotifications::sendVOObsoleteNotifications();
		}
	}

        /**
         * Replace new lines in details element of Secant report to html br tags
         *
         * @param  string $report Secant report data xml text
         * @return string
         */
        private function escapeSecantReport($report) {
            $res = trim($report);
            $matches = null;

            try {
                if ($res !== '') {
                    if (substr($res, 0, 1) !== '<') {
                        $result = base64_decode($res);
                        if ($result !== false) {
                            $res = trim($result);
                        }
                    }
                }

                if ($res !== '') {
                    $res = str_replace('<' . '?xml version="1.0" encoding="UTF-8"?' . '>', '', $res);
                    $res = trim($res);
                }


                if (preg_match_all("/\<DETAILS\>(.*?)\<\/DETAILS\>/sm", $report, $matches)) {
                    if ($matches) {
                        foreach($matches[1] as $m) {
                            if (trim($m)) {
                                $res = str_replace("<DETAILS>" . $m . "</DETAILS>", "<DETAILS>" .  trim(preg_replace('/\n/s', '&lt;br &gt;', trim($m))) . "</DETAILS>", $res);
                            }
                        }
                    }
                }
            } catch(Exception $e) {
                return trim($res);
            }

            return trim($res);
        }

        public function secantreportAction() {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();

            if($_SERVER['REQUEST_METHOD'] !== "GET" ){
                header('HTTP/1.0 404 Not Found');
                header("Status: 404 Not Found");
                return;
            }
            $appid = ((isset($_GET['appid']) && is_numeric($_GET['appid'])) ? intval($_GET['appid']) : -1);
            $void = ((isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id']) : -1);
            if ($void === -1) {
                header('HTTP/1.0 404 Not Found');
                header("Status: 404 Not Found");
                return;
            }

            $format = strtolower((isset($_GET['format']) && trim($_GET['format']) !== '') ? trim($_GET['format']) : 'xml');
            if ($format != 'js' && $format !== 'xml') {
                $format = 'xml';
            }

            $reportIds = strtolower((isset($_GET['reports']) && trim($_GET['reports']) !== '') ? trim($_GET['reports']) : '');
            if ($reportIds !== '') {
                $idarr = explode(';', $reportIds);
                $reportIds = array();
                foreach ($idarr as $rid) {
                    if (is_numeric($rid)) {
                        $reportIds[] = intval($rid);
                    }
                }
            } else {
                $reportIds = array();
            }

            $appidsql = '';
            if ($appid !== -1) {
                $appidsql = ' AND vaviews.appid = ' . $appid . ' ';
            }

            $reportidsql = '';
            if (count($reportIds) > 0) {
                $reportidsql = ' AND secant.id IN (' . implode(',', $reportIds) . ') ';
            }

            $secantstatesql = " AND secant.state <> 'aborted' ";
            if (count($reportIds) > 0) {
                $secantstatesql = " ";
            }

            $sql = "SELECT
              secant.id as report_id,
              vaviews.appid AS app_id,
              vaviews.appname AS app_name,
              vaviews.appcname AS app_cname,
              vaviews.vapplistid AS vapplist_id,
              vaviews.vappversionid AS vaversion_id,
              vaviews.va_version AS vaversion,
              CASE WHEN vowideitem.vapplistid = vaviews.vapplistid AND vowideitem.state = 'published' THEN 'current'
                   WHEN vaviews.va_version_archived = FALSE AND vaviews.va_version_published = TRUE THEN 'latest'
                   WHEN vaviews.va_version_archived = TRUE AND vaviews.va_version_published = TRUE THEN 'previous'
              END AS vaversion_type,
              vaviews.vmiinstanceid AS vmiinstance_id,
              vaviews.vmiinstance_guid AS vmiinstance_guid,
              vaviews.va_version_archived AS vmiinstance_archived,
              vowideitem.vowide_image_list_id AS vowideimagelist_id,
              secant.queuedon AS queuedon,
              secant.senton AS senton,
              secant.closedon AS closedon,
              secant.state AS state,
              secant.report_outcome AS report_outcome,
              secant.report_data AS report_data
            FROM vaviews
            LEFT OUTER JOIN (
              SELECT vowide_image_list_id, vapplistid, vowide_image_lists.state, void FROM vowide_image_lists
              INNER JOIN vowide_image_list_images ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
              WHERE (vowide_image_lists.state = 'draft' OR vowide_image_lists.state = 'published') AND vowide_image_lists.void = " . $void . "
              ) AS vowideitem ON vowideitem.vapplistid = vaviews.vapplistid
            INNER JOIN va_sec_check_queue AS secant ON secant.vmiinstanceid = vaviews.vmiinstanceid
            WHERE vaviews.va_version_published = true 
            AND vaviews.imglst_private = false " . $secantstatesql . "
            AND ((vowideitem.vapplistid = vaviews.vapplistid AND  vaviews.va_version_archived = TRUE) OR vaviews.va_version_archived = false) " . $appidsql . $reportidsql . " 
            ORDER BY vaviews.appid, vaviews.vapplistid";

            $rs = db()->query($sql, array())->toArray();
            $res = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
            $res .= "\n<result count='" . count($rs) . "'>\n";
            if (count($rs) > 0) {
                foreach ($rs as $r) {
                    $reportdata = trim($r["report_data"]);
                    if ($reportdata !== '') {
                        $reportdata = $this->escapeSecantReport($reportdata);
                    }
                    $res .= "  <report>\n";
                    $res .= "    <report_id>" . $r["report_id"] . "</report_id>\n";
                    $res .= "    <app_id>" . $r["app_id"] . "</app_id>\n";
                    $res .= "    <app_name>" . $r["app_name"] . "</app_name>\n";
                    $res .= "    <app_cname>" . $r["app_cname"] . "</app_cname>\n";
                    $res .= "    <vapplist_id>" . $r["vapplist_id"] . "</vapplist_id>\n";
                    $res .= "    <vaversion>" . $r["vaversion"] . "</vaversion>\n";
                    $res .= "    <vaversion_id>" . $r["vaversion_id"] . "</vaversion_id>\n";
                    $res .= "    <vaversion_type>" . $r["vaversion_type"] . "</vaversion_type>\n";
                    $res .= "    <vmiinstance_id>" . $r["vmiinstance_id"] . "</vmiinstance_id>\n";
                    $res .= "    <vmiinstance_guid>" . $r["vmiinstance_guid"] . "</vmiinstance_guid>\n";
                    $res .= "    <vmiinstance_archived>" . ($r["vmiinstance_archived"] == 1 ? 'true' : 'false') . "</vmiinstance_archived>\n";
                    $res .= "    <vowideimagelist_id>" . $r["vowideimagelist_id"] . "</vowideimagelist_id>\n";
                    $res .= "    <queuedon>" . $r["queuedon"] . "</queuedon>\n";
                    $res .= "    <senton>" . $r["senton"] . "</senton>\n";
                    $res .= "    <closedon>" . $r["closedon"] . "</closedon>\n";
                    $res .= "    <state>" . $r["state"] . "</state>\n";
                    $res .= "    <report_outcome>" . $r["report_outcome"] . "</report_outcome>\n";
                    $res .= "    <report_data>" . $reportdata . "</report_data>\n";
                    $res .= "</report>\n";
                }
            }
            $res .= "</result>";
            if ($format === 'xml') {
                header('Content-type: application/xml');
            } else {
                header('Content-type: application/json');
                $res = \RestAPIHelper::transformXMLtoJSON(trim($res));
            }

            echo $res;
        }
}
