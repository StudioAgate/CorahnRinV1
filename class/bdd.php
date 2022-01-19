<?php

namespace App;

use Exception;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Classe de connexion à la base de données
 * Permet la gestion de l'affichage des erreurs
 * Effectue des requêtes préparées
 * Simplifie les noms de tables avec les préfixes %% et % (voir méthode buildReq)
 */
class bdd
{

    public static $prefix;
    private $db;
    private $dbname;
    private $show_err;
    private $err_type;
    private $last_query;
    private $last_values;
    private $last_results;
    public $queriesRunnedCount = 0;
    public $queriesRunned = [];

    public function __construct(
        $host = '127.0.0.1',
        $user = 'root',
        $pwd = '',
        $database = 'mydb',
        $tb_prefix = '',
        $db_type = 'mysql'
    ) {
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        self::$prefix = $tb_prefix;
        $dsn = "$db_type:host=$host;dbname=$database;charset=UTF8";
        $this->initErr(true);
        try {
            $this->db = new PDO($dsn, $user, $pwd, $pdo_options);
            $this->dbname = $database;
        } catch (Exception $e) {
            $this->showErr($e, null);

            return;
        }
    }

    public function __toString()
    {
        $ret = '';
        $ret .= p_dump(
            [
                'database'     => $this->dbname,
                'show_err'     => $this->show_err,
                'err_type'     => $this->err_type,
                'last_query'   => $this->last_query,
                'last_values'  => $this->last_values,
                'last_results' => $this->last_results,
            ]
        );

        return $ret;
    }

    /**
     * Afficher les erreurs permet de débugguer plus rapidement les requêtes en mode développement. A désactiver en mode production
     *
     * @param boolean $err Affiche les erreurs ou non
     * @param string  $type Change le type d'erreur. Valeurs possible : 'fatal', 'warning', 'notice'
     */
    public function initErr($err = false, $type = 'fatal')
    {
        $this->show_err = (bool) $err;
        if ($type === 'warning') {
            $this->err_type = E_USER_WARNING;
        } elseif ($type === 'fatal') {
            $this->err_type = E_USER_ERROR;
        } elseif ($type === 'notice') {
            $this->err_type = E_USER_NOTICE;
        } else {
            $this->err_type = E_USER_WARNING;
        }
    }

    /**
     * Affiche les erreurs, renvoie le détail selon les paramètres envoyés  la méthode
     *
     * @param PDOException $e Utilisé en cas de throw exception sur une requête
     * @param string       $req_qry Utilisé pour afficher la requête en cas d'erreur, notamment sur les méthodes req(), row() et noRes()
     */
    public function showErr($e = null, $req_qry = null)
    {
        global $_PAGE;

        $final = '*|*|*Date=>'.json_encode(date(DATE_RFC822));
        $trace = is_object($e) ? $e->getTrace() : '';
        $final .= '||Erreur N°=>'.json_encode($e->errorInfo[0]);
        $final .= '||Traçage=>'.json_encode($trace);
        $final .= '||Méthode appelée=>'.json_encode($trace[2]['function']);
        $final .= '||Last instance query=>'.json_encode($this->last_query);
        $final .= '||Last real query=>'.json_encode($req_qry);
        $final .= '||Last values sent=>'.json_encode($this->last_values);
        $final .= '||PDO caught exceptions=>'.json_encode(is_object($e) ? $e->getMessage() : '');

        $final .= '||Ip=>'.json_encode($_SERVER['REMOTE_ADDR'])
            .'||Page.get=>'.json_encode($_PAGE['get'] ?? '__unknown__')
            .'||Page.request=>'.json_encode($_PAGE['request'] ?? '__unknown__')
            .'||User.id=>'.json_encode(Session::read('user'));

        $error_file = ROOT.DS.'logs'.DS.'sql'.DS.date('Y.m.d').'.log';
        if (!is_dir(dirname($error_file))) {
            FileAndDir::createPath(dirname($error_file));
            file_put_contents($error_file, '');
        }
        $f = fopen($error_file, 'ab');
        fwrite($f, $final);
        fclose($f);

        if (P_DEBUG) {
            throw new RuntimeException('An SQL error has occured...', 1, $e);
        }

        throw new RuntimeException('An SQL error has occured...', 1);
    }

    /**
     * Alias statique de buildReq() : Formate la requête
     *
     * @param string $req_qry La requête initiale
     * @param array  $values Les paramètres de la requête à préparer
     *
     * @return string Requête formatée
     */
    public static function sbuildReq($req_qry, $values = [])
    {
        $values = (array) $values;

        if (strpos($req_qry, '%%%fields') !== false) {//Transforme %%%Fields en une liste des champs à entrer
            $fields = [];
            foreach ($values as $field => $value) {
                $field = str_replace(':', '', $field);
                $fields[] = '%'.$field.' = :'.$field;
            }
            $req_qry = str_replace('%%%fields', implode(', ', $fields), $req_qry);
        }

        if (strpos($req_qry, '%%%in') !== false) {//Transforme %%%Fields en une liste des champs à entrer
            if (empty($values)) {
                $req_qry = str_replace('%%%in', '0', $req_qry);
            } else {
                $str = implode(', ', array_fill(0, count($values), '?'));
                $req_qry = str_replace('%%%in', $str, $req_qry);
            }
        }

        $req_qry = str_replace('%%', self::$prefix, $req_qry); // Transforme %%table en `prefix_table`
        $req_qry = preg_replace('#%(\w+)#', ' `$1` ', $req_qry); // Transforme %champ en `champ`

        foreach ($values as $k => $v) {
            if (!is_numeric($k) && strpos($k, ':') !== 0) {
                unset($values[$k]);
                $values[':'.$k] = $v;
            }
        }

        return $req_qry;
    }

    public function last_id()
    {
        try {
            $last_id = $this->db->lastInsertId();
            $last_id = (int) $last_id;
        } catch (Exception $e) {
            $last_id = false;
            $this->showErr($e, '');
        }

        return $last_id;
    }

    /**
     * Effectue une requête classique
     *
     * @param string $req_qry Une requête formatée préalablement avec buildReq()
     * @param mixed  $values Un tableau de valeurs à envoyer à PDO pour la requête préparée OU un identifiant
     *
     * @return array Un tableau avec une entrée pour chaque élément trouvé dans la BDD, false
     */
    public function req($req_qry, $values = [])
    {
        $values = (array) $values;
        $req_qry = $this->buildReq($req_qry, $values);
        $result = $this->runReq($req_qry, $values);
        if (is_object($result) && $result->rowCount() > 0) {
            $contents = $result->fetchAll();
            foreach ($contents as $key => $val) {
                foreach ($val as $vkey => $vval) {
                    if (is_numeric($vval)) {
                        $contents[$key][$vkey] = (int) $vval;
                    }
                    if (is_int($vkey)) {
                        unset($contents[$key][$vkey]);
                    }
                }
            }
        } elseif (is_array($result)) {
            $contents = $result;
        } else {
            $contents = false;
        }
        $this->last_results = $contents;
        if (is_object($result)) {
            $result->closeCursor();
        }

        return $contents;
    }

    /**
     * Effectue une requête mais ne récupère que le premier résultat. Utile pour les sélections uniques par Id
     *
     * @param string $req_qry Une requête formatée préalablement avec buildReq()
     * @param mixed  $values Un tableau de valeurs à envoyer à PDO pour la requête préparée
     *
     * @return array à 1 entrée, false sinon
     */
    public function row($req_qry, $values = [])
    {
        $values = (array) $values;
        $req_qry = $this->buildReq($req_qry, $values);

        if (!preg_match('#LIMIT +\d+( *, *\d+)?#iU', $req_qry)) {
            $req_qry .= ' LIMIT 0,1';
        }

        $result = $this->runReq($req_qry, $values);
        if (is_object($result) && $result->rowCount() > 0) {
            $contents = $result->fetch();
            foreach ($contents as $key => $val) {
                if (is_numeric($val)) {
                    $contents[$key] = (int) $val;
                }
                if (is_int($key)) {
                    unset($contents[$key]);
                }
            }
        } elseif (is_array($result)) {
            $contents = $result;
        } else {
            $contents = false;
        }
        $this->last_results = $contents;
        if (is_object($result)) {
            $result->closeCursor();
        }

        return $contents;
    }

    /**
     * Effectue une requête mais ne récupère pas de résultat autre que la réussite ou l'échec. Utile pour update,insert,set,delete...
     *
     * @param string $req_qry Une requête formatée préalablement avec buildReq()
     * @param mixed  $values Un tableau de valeurs à envoyer à PDO pour la requête préparée
     *
     * @return bool true si la requête est excéutée, false sinon
     */
    public function noRes($req_qry, $values = [])
    {
        $values = (array) $values;
        $req_qry = $this->buildReq($req_qry, $values);
        $result = $this->runReq($req_qry, $values);
        if (is_object($result) && $result->rowCount() > 0) {
            $results = $result->rowCount();
        } elseif (is_array($result)) {
            $results = $result;
        } else {
            $results = false;
        }
        $this->last_results = $results;
        if ($results) {
            $result->closeCursor();
        }

        return (bool) $results;
    }

    /**
     * Formate la requête
     *
     * @param string $req_qry La requête initiale
     * @param array  $values Les paramètres de la requête à préparer
     *
     * @return string Requête formatée
     */
    private function buildReq($req_qry, $values = [])
    {
        $req_qry = self::sbuildReq($req_qry, $values);
        $this->last_query = $req_qry;
        $this->last_values = $values;

        return $req_qry;
    }

    /**
     * Prépare une requête et l'exécute via PDO
     *
     * @param string $req_qry Requête SQL (Doit avoir été formatée par la méthode buildReq)
     * @param array  $values Tableau de valeurs à envoyer à PDO pour l'exécution
     *
     * @return object PDOStatement
     */
    private function runReq($req_qry, $values = [])
    {
        $values = (array) $values;
        try {
            $result = $this->db->prepare($req_qry);
            $result->execute($values);
        } catch (Exception $e) {
            $result = false;
            $this->showErr($e, $req_qry);
        }
        $this->queriesRunnedCount++;
        $this->queriesRunned[] = $req_qry.' ['.implode(',', $values).']';

        return $result;
    }

}
