<?php
namespace HHK\Member\ProgressiveSearch\SearchNameData;

use HHK\SysConst\GLTableNames;
use HHK\sec\Session;

class SearchResults extends SearchNameData
{
    
    protected $noReturn;
    protected $id;
    protected $psgId;

    
    
    /**
     * @return mixed
     */
    public function getNoReturn()
    {
        return $this->noReturn;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPsgId()
    {
        return $this->psgId;
    }

    /**
     * @param mixed $noReturn
     */
    public function setNoReturn($noReturn)
    {
        $this->noReturn = $noReturn;
        return $this;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $psgId
     */
    public function setPsgId($psgId)
    {
        $this->psgId = $psgId;
        return $this;
    }

    public function setNameFirst($nameFirst) {
        $this->nameFirst = preg_replace_callback("/(&#[0-9]+;)/",
            function($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            },
            $nameFirst
            );
        return $this;
    }
    
    /**
     * @param string $nameMiddle
     */
    public function setNameMiddle($nameMiddle) {
        $this->nameMiddle =  preg_replace_callback("/(&#[0-9]+;)/",
            function($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            },
            $nameMiddle
            );
        return $this;
    }
    
    /**
     * @param string $nameLast
     */
    public function setNameLast($nameLast) {
        $this->nameLast =  preg_replace_callback("/(&#[0-9]+;)/",
            function($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            },
            $nameLast
            );
        return $this;
    }
    
    /**
     * @param string $nickname
     */
    public function setNickname($nickname) {
        $this->nickname =  preg_replace_callback("/(&#[0-9]+;)/",
            function($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            },
            $nickname
            );
        return $this;
    }
    
    public function getBirthDate() {
        
        if ($this->birthDate != '') {
            return date('M d, Y', strtotime($this->birthDate));
        }
        
        return $this->birthDate;
    }
    
    public function getPhone() {
        return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $this->phone);
    }
    
    public function getRelationship() {
        
        $uS = Session::getInstance();
        
        if (isset($uS->guestLookups[GLTableNames::PatientRel][$this->relationship])) {
            return $uS->guestLookups[GLTableNames::PatientRel][$this->relationship][1];
        }
        
        return $this->relationship;
        
    }
}
