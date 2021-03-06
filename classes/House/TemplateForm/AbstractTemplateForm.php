<?php

namespace HHK\House\TemplateForm;

/**
 * AbstractTemplateForm.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2018 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

/**
 * Description of TemplateForm
 *
 * @author Eric
 */
abstract class AbstractTemplateForm {

    public $docId;
    public $template;
    public $replacedTemplate;
       
   /**
    * @param \PDO $dbh
    * @param integer $docId
    */
   function __construct(\PDO $dbh, $docId){
       
       if(intval($docId) > 0 && $dbh){
           $stmt = $dbh->query("Select `Doc` from `document` where `idDocument` = $docId");
           $docRow = $stmt->fetch(\PDO::FETCH_ASSOC);
           if(isset($docRow['Doc'])){
               $this->template = $docRow['Doc'];
           }else{
               $this->template = "";
           }
       }else{
           $this->template = "";
       }
       
   }
    
    public function createForm($replacements) {

        $this->replacedTemplate = $this->template;
        $vars = $this->getVariables();

        foreach ($vars as $v) {

            if (isset($replacements[$v])) {
                $this->setValue($v, $replacements[$v]);
            }
        }

        return str_replace('  ', ' ', $this->replacedTemplate);
    }

    protected function setValue($search, $replace) {

        $this->replacedTemplate = str_replace(self::ensureMacroCompleted($search), $replace, $this->replacedTemplate);

    }

    protected static function ensureMacroCompleted($macro) {

        if (substr($macro, 0, 2) !== '${' && substr($macro, -1) !== '}') {
            $macro = '${' . $macro . '}';
        }

        return $macro;
    }

    protected function getVariables() {

        $matches = array();

        preg_match_all('/\$\{(.*?)}/i', $this->template, $matches);

        return array_unique($matches[1]);

    }
}
