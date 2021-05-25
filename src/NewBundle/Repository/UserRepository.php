<?php

namespace NewBundle\Repository;

use Doctrine\DBAL\DBALException;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository{
    
    /**
     * Gets the users list for the Datatable. It creates 3 different querys, with all the
     * information needed for the table.
     *
     * @param json $params
     * @return json_data
     */
    public function getUsersList($params){
        $qr = $this->getEntityManager()->getConnection(); 

        $columns = [
            0 => "u.username",
            1 => "u.first_name",
            2 => "u.last_name",
            3 => "u.email",
            4 => "u.role",
            5 => "u.created_at",
            6 => "u.updated_at"
        ];

        $where = $sqlTot = $sqlRec = "";
        
        if(isset($params['search']) && $params['search']['value'] != '' ){
            $form_filters = $params ['search']['value'];
            $form_filters = json_decode ($form_filters, true);
        }

        if (isset($form_filters['username']) AND !empty($form_filters['username'])){
            $where .= " AND ( u.username LIKE '%". trim($form_filters['username']) ."%')" ;
        }
        if (isset($form_filters['first_name']) AND !empty($form_filters['first_name'])){
            $where .= " AND ( u.first_name LIKE '%". trim($form_filters['first_name']) ."%')" ;
        }
        if (isset($form_filters['last_name']) AND !empty($form_filters['last_name'])){
            $where .= " AND ( u.last_name LIKE '%". trim($form_filters['last_name']) ."%')" ;
        }
        if (isset($form_filters['email']) AND !empty($form_filters['email'])){
            $where .= " AND ( u.email LIKE '%". trim($form_filters['email']) ."%')" ;
        }
        if (isset($form_filters['role']) AND !empty($form_filters['role'])){
            $where .= " AND ( u.role LIKE '%". trim($form_filters['role']) ."%')" ;
        }
        if (isset($form_filters['created_at']) AND !empty($form_filters['created_at'])){
            $where .= " AND ( u.created_at LIKE '%". trim($form_filters['created_at']) ."%')" ;
        }
        if (isset($form_filters['updated_at']) AND !empty($form_filters['updated_at'])){
            $where .= " AND ( u.updated_at LIKE '%". trim($form_filters['updated_at']) ."%')" ;
        }

        if (isset($form_filters['is_active']) AND !empty($form_filters['is_active'])){
            if($form_filters['is_active'] == 'true'){
                $where .= " AND ( u.is_active = true)" ;
            } else{
                $where .= " AND ( u.is_active = false)" ;
            }
                
        }

        $sql = " SELECT u.username,
                        u.first_name,
                        u.last_name,
                        u.email,
                        u.role,
                        u.created_at,
                        u.updated_at,
                        u.is_active,
                        u.id
                 FROM users AS u
                 WHERE u.is_deleted = 0
               ";
        
        try {
            $sqlTot .= $sql;
            $sqlRec .= $sql;

            if (isset($where) && $where != ''){
                $sqlRec .= $where;
            }

            if (isset($params['order'])){
                $sqlRec .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'];
            } else{
                $sqlRec .= " ORDER BY u.created_at DESC ";
            }

            $limit = " ";

            if($params['length'] != -1){
                $limit = " LIMIT " . $params['start'] . " ," . $params['length'];
            }

            $sqlRecTot = $sqlRec;
            $sqlRec .= $limit;

            $queryTot = $qr->executeQuery($sqlTot)->rowCount();
            $queryRecordsTot = $qr->executeQuery($sqlRecTot)->rowCount();
            $data = $qr->executeQuery($sqlRec)->fetchAll(\PDO::FETCH_ASSOC);

            $json_data = array(
                "draw" => intval($params['draw']),
                "recordsTotal" => intval($queryTot),
                "recordsFiltered" => intval($queryRecordsTot),
                "data" => $data
            );
        } catch (DBALException $e){
            $json_data = $e->getMessage();
        }

        return $json_data;
    }
}
