<?php
namespace Pw\DataTable;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiDataTable extends Bundle  {
    
    public function get($params=[])
    {
        $result = [
            'status' => 200,
            'message' => '',
            'datas' => [],
            'page' => 1,
            'limit' => 10,
            'total' => 0,
            'totalFiltered' => 0,
        ];
        if(isset($params['entity']) && isset($params['em'])) {
            $em = $params['em'];
            $query = $params['query'];
            $entityClass = $params['entity'];

            if (class_exists($entityClass)) {
                $entityInstance = new $entityClass();
                $tableName = $this->getEnityName($entityInstance, $em);
                $columns = $this->getColumnNames($entityInstance, $em);
                $repository = $em->getRepository(get_class($entityInstance));
                $qb = $repository->createQueryBuilder($tableName);

                $key = isset($query['key']) ? $query['key'] : null ;
                $page = isset($query['page']) ? $query['page'] : 1 ;
                $limit = isset($query['limit']) ? $query['limit'] : 10 ;
                $ordering = isset($query['order']) ? $query['order'] : 'DESC' ;
                $filters = isset($query['filters']) ? $query['filters'] : null ;
                $order_by = isset($query['order_by']) ? $query['order_by'] : null ;

                // Add conditions for search
                if ($key) {
                    $qb = $this->getSearchQuery($qb, $tableName, $columns, $key);
                }

                // Add conditions for filter
                if ($filters) {
                    $qb = $this->getFiltreQuery($qb, $tableName, $columns, $filters);
                }

                // Add an ORDER BY clause
                if ($order_by && in_array($order_by, $columns)) {
                    $qb->orderBy($tableName.'.'.$order_by, $ordering);
                }

                // Query not OFFSET and LIMIT clauses
                $qdTotal = $qb->getQuery()->getResult();


                // Set the OFFSET and LIMIT clauses
                $offset = $this->getOffset($page, $limit);
                $qb->setFirstResult($offset);
                $qb->setMaxResults($limit);

                // Execute the query and get the results
                $total = sizeof($qdTotal);
                $results = $qb->getQuery()->getResult();
                $datas = $this->getColumnNamesValue($results, $columns);
                $nbrPagination = $this->getTotalFiltered($total, $limit);


                $result['page'] = $page;
                $result['datas'] = $datas;
                $result['total'] = $total;
                $result['limit'] = $limit;
                $result['totalFiltered'] = $total;
                $result['nbrPagination'] = intval($nbrPagination) ;

            } else {
                $result['status'] = 500;
                $result['message'] = "La classe $entityClass n'existe pas.";
            }

        } else {
            $result['status'] = 500;
            $result['message'] = "La Varibale entity n'existe pas.";
        }
        
        return $result ;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return int
     */
    function getOffset($page, $limit){
        $offset = ($page - 1) * $limit;
        return $offset;
    }

    /**
     * @param object $entityInstance
     * @param ManagerRegistry $em
     * @return string
     */
    function getEnityName($entityInstance, $em){
        $name = $em->getClassMetadata(get_class($entityInstance))->getTableName();
        return $name;
    }

    /**
     * @param object $entityInstance
     * @param ManagerRegistry $em
     * @return array
     */
    function getColumnNames($entityInstance, $em){
        $cols = $em->getClassMetadata(get_class($entityInstance))->getFieldNames();
        return $cols;
    }

    /**
     * @param object $entitys
     * @param array $cols
     * @return array
     */
    function getColumnNamesValue($entitys = null, $cols = []){
        $datas = array();
        if($entitys) {
            foreach($entitys as $entity){
                $values = array();
                foreach($cols as $col){
                    $getter = 'get'.$this->underscoreToCamelCaseAll($col);
                    if(!method_exists($entity, $getter)) {
                        $getter = 'is'.$this->underscoreToCamelCaseAll($col);
                    }
                    if(method_exists($entity, $getter)) {
                        $values[$col] = $entity->$getter();
                    }
                }
                $datas[] = $values;
            } 
        }
        return $datas;
    }
    
    /**
     * @param object $qb
     * @param string $tableName
     * @param array $columns
     * @param string $key
     * @return object
     */
    function getSearchQuery($qb, $tableName, $columns, $key){
        $searchTerm = "key".bin2hex(random_bytes(5)) ;
        foreach($columns as $column){
                $qb->orWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like($tableName.'.'.$column, ":$searchTerm")
                    )
                )->setParameter($searchTerm, '%' . $key . '%');
        }
        return $qb;
    }

    /**
     * @param object $qb
     * @param string $tableName
     * @param array $columns
     * @param array $filters
     * @return object
     */
    function getFiltreQuery($qb, $tableName, $columns, $filters){
        /*$filters = [
            [
                'key' => 'nom',
                'value' => 'MIMIAH'
            ]
        ];*/
        if ($filters && is_array($filters)) {
            foreach ($filters as $filter) {
                if ( 
                    $filter['key'] 
                    && $filter['value']
                    && in_array($filter['key'], $columns)
                    && trim($filter['value']) != ""
                ) {
                    $value = "value".bin2hex(random_bytes(5)) ;
                    $filter_key = trim($filter['key']);
                    $filter_value = trim($filter['value']);

                    $qb->andWhere(
                        $qb->expr()->andX(
                            $qb->expr()->like($tableName.'.'.$filter_key, ":$value")
                        )
                    )->setParameter($value, $filter_value);
                }
            }
        }
        return $qb;
    }

    /**
     * @param int $totalItems
     * @param int $itemsPerPage
     * @return int
     */
    function getTotalFiltered($totalItems, $itemsPerPage) {
        $numPages = ceil($totalItems / $itemsPerPage);
        return $numPages;
    }

    function underscoreToCamelCaseAll($str) {
        return str_replace('_', '', ucwords($str, '_'));
    }
    
}