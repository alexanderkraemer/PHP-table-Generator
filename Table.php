<?php

class Table
{
    private $table;
    private $primaryKey;
    private $columns;
    private $searchParams;
    private $data;
    public  $database;
    private $detailPage;
    private $nextOrder;
    private $orderImg;
    private $currentOrder;
    private $rowsPerPage;
    private $pageNr;
    private $queryCount;
    private $compareColumns;
    private $rule;
    private $likeZusatz;
    private $orZusatz;

    function __construct ( $table, $primaryKey, $columns, $detailPage, $whereZusatz, $likeZusatz, $rule, $orZusatz )
    {
        $this->rule        = $rule;
        $this->database    = new Database(); // Connection to Database is in a different class.
        $this->whereZusatz = $whereZusatz;
        $this->primaryKey  = $primaryKey;
        $this->detailPage  = $detailPage;
        $this->likeZusatz  = $likeZusatz;
        $this->table       = $table;
        $this->columns     = $columns;
        $this->queryCount  = $this->countQuery ();
        $this->orZusatz    = $orZusatz;
        $data              = $this->getDataBySql ();
    }

    public function getDataBySql ()
    {
        $sql        = $this->generateQuery ();
        $return     = $this->queryAndFetchObjectArray ();
        $this->data = $return;

        return $return;
    }

    private function queryAndFetchObjectArray ()
    {
        $stmt = $this->database->conn->prepare ( $this->generateQuery () );
        $stmt->execute ( $this->searchParams );
        $result = $stmt->fetchAll ( PDO::FETCH_ASSOC );
        $data   = array ();
        $k      = 0;
        foreach ( $result as $row )
        {
            $data[ $k ] = $this->createObject ( $row );
            $k++;
        }

        return $data;
    }

    private function createObject ( $data )
    {

        $table = new ObjectArray();
        foreach ( $data as $key => $value )
        {
            $table->setColName ( $key, $value );
        }

        return $table;
    }

    public function build ()
    {
        $data = $this->data;
        if ( isset( $_GET['search'] ) )
        {
            $search = $_GET['search'];
        }
        else
        {
            $search = '';
        }
        $html = '';
        $html .= '<table id="general-table" class="table table-striped table-vcenter table-striped table-bordered table-hover">';
        $html .= '<thead>';

        if ( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] !=
             $_SERVER['HTTP_HOST'] . explode ( '?', $_SERVER['REQUEST_URI'] )[0]
        )
        {
            if ( isset( $this->rule ) AND ! empty( $this->rule ) )
            {
                if ( $this->rule == 'gp' )
                {
                    $rule = '&tab=gesprchsprotokoll';
                }
                else
                {
                    $rule = '';
                }
            }
            else
            {
                $rule = '';
            }
            $html .= '<th style="padding:7px;"><a href="http://' . $_SERVER['HTTP_HOST'] .
                     explode ( '?', $_SERVER['REQUEST_URI'] )[0] . $rule . '">
       <input style="height:20px; font-size:12px; padding-top:1px; border:2px solid red;" type="button"
       class="btn btn-primary" value="clear Filter">
      </a></th>';
        }
        else
        {
            $html
                .= '<th style="padding:7px;"><input style="font-weight:bold; color:#ccc; height:20px; font-size:12px;
                   padding-top:1px; width:100%;"  
                                type="button"  class="btn btn-primary" disabled value="clear Filter"></th>';
        }
        /*
         * Hier werden die Spaltennamen in die Tabelle geschrieben
         */
        foreach ( $this->columns as $columnName )
        {
            if ( ! empty( $columnName['name'] ) )
            {
                $html .= '<th style="padding:7px; font-size:13px;">';
                $string = array ();
                if ( isset( $_GET['rowsPerPage'] ) AND ! empty( $_GET['rowsPerPage'] ) )
                {
                    $string[] = '&rowsPerPage=' . $_GET['rowsPerPage'];
                }
                if ( isset( $_GET['search'] ) AND ! empty( $_GET['search'] ) )
                {
                    $string[] = '&search=' . $_GET['search'];
                }

                foreach ( $this->getParams () as $key => $value )
                {
                    /*
                     * Sonderspalten
                     */
                    if ( $key == 'auf_wer' )
                    {
                        foreach ( $_GET[ $key ] as $k )
                        {
                            $string[] .= $key . '%5B%5D=' . $k;
                        }
                    }
                    elseif ( $key == 'auf_delegieren' )
                    {
                        foreach ( $_GET[ $key ] as $k )
                        {
                            $string[] .= $key . '%5B%5D=' . $k;
                        }
                    }
                    else
                    {
                        $string[] = $key . '=' . $value;
                    }
                }
                $html .= '<a href="?column=' . $columnName['db'] . '&order=' . $this->nextOrder . '&' .
                         implode ( '&', $string ) . '">';
                if ( isset( $_GET['order'] ) AND ( $columnName['db'] == $this->currentOrder ) )
                {
                    $html .= $columnName['name'] . ' ' . $this->orderImg;
                }
                else
                {
                    $html .= $columnName['name'];
                }
                $html .= '</a></th>';
            }
        }
        $html .= '<tr>';
        /*
         * Form für die Filterung pro Spalte
         */
        $html .= '<form method="GET" action="" >';
        $html
            .= '<td style="padding:7px; width:100px;"><input class="btn btn-primary pull-right"  style="height:34px; font-size:12px;
                   padding-top:4px; width:100%;" 
                  type="submit" value="Search" ></td>';
        foreach ( $this->columns as $columnName )
        {
            if ( ! empty( $columnName['name'] ) )
            {
                if ( isset( $_GET[ $columnName['db'] ] ) )
                {
                    $getSearch = $_GET[ $columnName['db'] ];
                }
                else
                {
                    $getSearch = '';
                }

                $html
                    .= '<td style="padding:7px;"><input class="form-control" autocomplete="off" style="height:34px;
                    margin:0px; padding:3px;" type="search" value="' . $getSearch . '"
                    placeholder="' . $columnName['name'] . '" name="' . $columnName['db'] . '"></td>';

            }
        }
        if ( isset( $_GET['rowsPerPage'] ) AND ! empty( $_GET['rowsPerPage'] ) )
        {
            $html .= '<input type="hidden" name="rowsPerPage" value="' . $_GET['rowsPerPage'] . '">';
        }
        else
        {
            $html .= '<input type="hidden" name="rowsPerPage" value="10">';
        }
        if ( isset( $_GET['search'] ) AND ! empty( $_GET['search'] ) )
        {
            $html .= '<input type="hidden" name="search" value="' . $_GET['search'] . '">';
        }
        if ( isset( $_GET['order'] ) AND ! empty( $_GET['order'] ) )
        {
            $html .= '<input type="hidden" name="order" value="' . $_GET['order'] . '">';
        }
        if ( isset( $_GET['column'] ) AND ! empty( $_GET['column'] ) )
        {
            $html .= '<input type="hidden" name="column" value="' . $_GET['column'] . '">';
        }
        if ( isset( $this->rule ) AND ! empty( $this->rule ) )
        {
            $html .= '<input type="hidden" name="tab" value="gesprchsprotokoll">';
        }
        $html .= '</form>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        if ( empty( $data ) )
        {
            $html .= '<tr><td style="text-align:center;" colspan="' . ( count ( $this->columns ) ) .
                     '">Keine Einträge vorhanden</td></tr>';
        }
        foreach ( $data as $daten )
        {
            $prim = $this->primaryKey;
            $html .= '<tr onclick="document.location = \'' . $this->detailPage . '/' . $daten->$prim .
                     '\';" style="font-size:12px;">';
            if ( $this->rule == 'reactivate' )
            {
                $html .= '<td style="text-align:center;"><form method="post" action="">';
                $html .= '<input type="hidden" name="unlock" value="' . $daten->$prim . '">';
                $html .= '<button style="background:transparent; border:none;" type="submit"><i class="fa fa-unlock"></i></button>';
                $html .= '</form></td>';
            }
            else
            {
                $html .= '<td style="vertical-align:top; text-align:center;"><a href="' . $this->detailPage . '/' .
                         $daten->$prim . '">';
                $html .= '<i class="glyphicon glyphicon-eye-open"></i>';
                $html .= '</a></td>';
            }
            foreach ( $this->columns as $columnName )
            {
                $html .= '<td style="vertical-align:top;">' . $daten->$columnName['db'] . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr>';
        $html .= '<td style="border-right:0px;" colspan="' . ( count ( $this->columns ) + 1 ) . '">';
        $html .= $this->queryCount;
        $html .= ' Rows';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="text-left; border-right:0px;" colspan="' . ( count ( $this->columns ) + 1 ) . '">';
        if ( ! isset( $_GET['pageNr'] ) OR $this->pageNr == 1 )
        {
            $prev = 1;
        }
        else
        {
            $prev = $this->pageNr - 1;
        }
        $anzahlAnSeiten = ceil ( $this->queryCount / $this->rowsPerPage );
        if ( $anzahlAnSeiten <= $this->pageNr )
        {
            $next = $this->pageNr;
        }
        else
        {
            $next = $this->pageNr + 1;
        }
        $string = array ();
        foreach ( $this->getParams () as $key => $value )
        {
            $string[] = $key . '=' . $value;
        }
        $html .= '<a class="pull-left" href="?pageNr=' . $prev . '&' . implode ( '&', $string ) .
                 '" style="margin-left:20px; text-decoration:none;"><< Previous</a>';
        $string = array ();
        foreach ( $this->getParams () as $key => $value )
        {
            $string[] = $key . '=' . $value;
        }
        $html .= '<a class="pull-right" href="?pageNr=' . $next . '&' . implode ( '&', $string ) . '" style="margin-right:20px; text-decoration:none;">Next >></a>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="text-align:left; border-left:0px;" colspan="' . ( count ( $this->columns ) + 1 ) . '">';
        $html .= '<form method="GET">';
        $html .= '<select style="width:60px;" name="rowsPerPage" onchange="this.form.submit()">';
        $arrayRowsPerPage = array ( 10, 25, 50, 100, 200 );
        foreach ( $arrayRowsPerPage as $rows )
        {
            if ( isset( $_GET['rowsPerPage'] ) AND $_GET['rowsPerPage'] == $rows )
            {
                $html .= '<option selected>' . $rows . '</option>';
            }
            else
            {
                $html .= '<option>' . $rows . '</option>';
            }
        }
        $html .= '</select>';
        foreach ( $this->getParams () as $key => $value )
        {
            if ( ( ! empty( $_GET[ $key ] ) ) )
            {
                $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            }
        }
        if ( isset( $_GET['search'] ) AND ! empty( $_GET['search'] ) )
        {
            $html .= '<input type="hidden" name="search" value="' . $_GET['search'] . '">';
        }
        if ( isset( $_GET['order'] ) AND ! empty( $_GET['order'] ) )
        {
            $html .= '<input type="hidden" name="order" value="' . $_GET['order'] . '">';
        }
        if ( isset( $_GET['column'] ) AND ! empty( $_GET['column'] ) )
        {
            $html .= '<input type="hidden" name="column" value="' . $_GET['column'] . '">';
        }
        $html .= ' rows per page';
        $html .= '</form>';
        //$html .= '</td>';
        //$html .= '<td colspan="'.(count($this->columns)-1).'">';
        $html .= '<form method="GET" action="">';
        $html .= '<select style="width:60px;" name="pageNr" onchange="this.form.submit()">';
        if ( $anzahlAnSeiten == 0 )
        {
            $html .= '<option>1</option>';
        }
        for (
            $i = 1; $i <= $anzahlAnSeiten; $i++ )
        {
            if ( isset( $_GET['pageNr'] ) AND $_GET['pageNr'] == $i )
            {
                $html .= '<option selected>' . $i . '</option>';
            }
            else
            {
                $html .= '<option>' . $i . '</option>';
            }
        }
        $html .= '</select>';
        foreach ( $this->getParams () as $key => $value )
        {
            if ( ( ! empty( $_GET[ $key ] ) ) )
            {
                $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            }
        }
        if ( isset( $_GET['search'] ) AND ! empty( $_GET['search'] ) )
        {
            $html .= '<input type="hidden" name="search" value="' . $_GET['search'] . '">';
        }
        if ( isset( $_GET['rowsPerPage'] ) AND ! empty( $_GET['rowsPerPage'] ) )
        {
            $html .= '<input type="hidden" name="rowsPerPage" value="' . $_GET['rowsPerPage'] . '">';
        }
        if ( isset( $_GET['order'] ) AND ! empty( $_GET['order'] ) )
        {
            $html .= '<input type="hidden" name="order" value="' . $_GET['order'] . '">';
        }
        if ( isset( $_GET['column'] ) AND ! empty( $_GET['column'] ) )
        {
            $html .= '<input type="hidden" name="column" value="' . $_GET['column'] . '">';
        }
        if ( isset( $this->rule ) AND ! empty( $this->rule ) )
        {
            $html .= '<input type="hidden" name="tab" value="gesprchsprotokoll">';
        }
        $html .= ' go to page';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html .= '';
        $html .= '';
        $html .= '';
        $html .= '</table>';

        return $html;
    }

    private function getParams ()
    {
        $spalten = array ();
        foreach ( $this->columns as $columnnamen )
        {
            $spalt[] = $columnnamen['db'];
        }
        foreach ( $spalt as $spaltenNamen )
        {
            if ( isset( $_GET[ $spaltenNamen ] ) AND ! empty( $_GET[ $spaltenNamen ] ) )
            {
                $spalten[ $spaltenNamen ] = $_GET[ $spaltenNamen ];
            }
        }

        return $spalten;
    }

    private function limit ()
    {
        if ( isset( $_GET['rowsPerPage'] ) )
        {
            $rowsPerPage = $_GET['rowsPerPage'];
        }
        else
        {
            $rowsPerPage = 10;
        }
        if ( isset( $_GET['pageNr'] ) AND ( $_GET['pageNr'] > 0 ) )
        {
            $pageNr = $_GET['pageNr'];
        }
        else
        {
            $pageNr = 1;
        }
        $this->pageNr      = $pageNr;
        $this->rowsPerPage = $rowsPerPage;
        $offset            = $rowsPerPage * $pageNr - $rowsPerPage;
        $limit             = "LIMIT " . $offset . ", " . $rowsPerPage;

        return $limit;
    }

    private function order ()
    {
        if ( isset( $_GET['order'] ) )
        {
            $order = $_GET['order'];
        }
        elseif ( $this->rule == 'asc' )
        {
            $order = 'asc';
        }
        elseif ( $this->rule == 'desc' )
        {
            $order = 'desc';
        }
        else
        {
            $order = 'asc';
        }
        if ( $order == 'asc' )
        {
            $this->nextOrder = 'desc';
            $this->orderImg  = '&#9650;';
        }
        else
        {
            $this->nextOrder = 'asc';
            $this->orderImg  = '&#9660;';
        }
        if ( isset( $_GET['column'] ) )
        {
            $column = $_GET['column'];
        }
        if ( ! isset( $column ) )
        {
            $column = $this->primaryKey;
        }
        else
        {
            for (
                $i = 0; $i < count ( $this->columns ); $i++ )
            {
                if ( $this->columns[ $i ]['db'] != $column )
                {
                    $bool = false;
                }
                else
                {
                    $bool = true;
                    break;
                }
            }
            if ( ! $bool )
            {
                $column = $this->primaryKey;
            }
        }
        $this->currentOrder = $column;
        $order              = 'ORDER BY ' . $column . ' ' . $order;

        return $order;
    }

    private function searchColumn ()
    {
        for (
            $i = 0; $i < count ( $this->columns ); $i++ )
        {
            if ( isset( $_GET[ $this->columns[ $i ]['db'] ] ) )
            {
                $getValue[ $this->columns[ $i ]['db'] ] = $_GET[ $this->columns[ $i ]['db'] ];
            }
            else
            {
                $getValue[ $this->columns[ $i ]['db'] ] = '';
            }
            if ( $this->columns[ $i ]['db'] )
            {
                $bool = true;
            }
            else
            {
                $bool = false;
            }
            if ( $bool )
            {
                if ( ! empty( $this->columns[ $i ]['name'] ) )
                {
                    $searchStr[] = $this->columns[ $i ]['db'] . ' LIKE ?';
                }
            }
        }
        for (
            $k = 0; $k < count ( $this->columns ); $k++ )
        {
            if ( ! empty( $this->columns[ $k ]['name'] ) )
            {
                $columnnames[] = $this->columns[ $k ]['db'];
            }
        }
        foreach ( $columnnames as $column )
        {
            if ( isset( $_GET[ $column ] ) )
            {
                $searchColumnParams[] = '%' . $_GET[ $column ] . '%';
            }
            else
            {
                $searchColumnParams[] = '%%';
            }
        }
        foreach ( $this->likeZusatz as $keyLike => $valueLike )
        {
            $searchColumnParams[] = '%' . $valueLike . '%';
        }
        foreach ( $this->whereZusatz as $keyWhere => $valueWhere )
        {
            $searchColumnParams[] = $valueWhere;
        }
        if ( empty( $this->orZusatz ) )
        {
            $this->orZusatz = array ();
        }
        foreach ( $this->orZusatz as $keyOr => $valueOr )
        {
            foreach ( $valueOr as $valOr )
            {
                $searchColumnParams[] = $valOr;
            }
        }
        $columnString = '(' . implode ( ' AND ', $searchStr ) . ')';
        $return       = array ( 'searchString' => $columnString, 'params' => $searchColumnParams );

        return $return;
    }

    private function generateQuery ()
    {
        $paramLike = array ();
        foreach ( $this->likeZusatz as $key => $value )
        {
            $paramLike[] = $key . ' LIKE ?';
        }
        $likeZusatz = implode ( ' AND ', $paramLike );
        if ( ! empty( $likeZusatz ) )
        {
            $likeZusatz = ' AND ' . $likeZusatz;
        }
        $paramWhere = array ();
        foreach ( $this->whereZusatz as $key => $value )
        {
            $paramWhere[] = $key . ' = ?';
        }
        $whereZusatz = implode ( ' AND ', $paramWhere );
        if ( ! empty( $whereZusatz ) )
        {
            $whereZusatz = '  AND  ' . $whereZusatz;
        }
        $paramOr = array ();
        if ( empty( $this->orZusatz ) )
        {
            $this->orZusatz = [ ];
        }
        foreach ( $this->orZusatz as $key => $value )
        {
            foreach ( $value as $val )
            {
                $paramOr[] = $key . ' != ?';
            }
        }
        $orZusatz = implode ( ' AND ', $paramOr );
        if ( ! empty( $orZusatz ) )
        {
            $orZusatz = '  AND  (' . $orZusatz . ')';
        }
        for (
            $i = 0; $i < count ( $this->columns ); $i++ )
        {
            $colArr[] = $this->columns[ $i ]['db'];
        }
        $columns = implode ( ', ', $colArr );
        $tables  = implode ( ' INNER JOIN ', $this->table );

        $sql = 'SELECT ' . $columns . ' FROM ' . $tables . ' WHERE ';

        $sql .= $this->searchColumn ()['searchString'] . '  ' . $likeZusatz . '  ' . $whereZusatz . ' ' .
                $orZusatz . '  GROUP BY ' . $this->primaryKey . ' ' . $this->order () . ' ' . $this->limit ();

        $this->searchParams = $this->searchColumn ()['params'];

        return $sql;
    }

    private function countQuery ()
    {
        $paramLike = array ();
        foreach ( $this->likeZusatz as $key => $value )
        {
            $paramLike[] = $key . ' LIKE ?';
        }
        $likeZusatz = implode ( ' AND ', $paramLike );
        if ( ! empty( $likeZusatz ) )
        {
            $likeZusatz = ' AND ' . $likeZusatz;
        }
        $paramWhere = array ();
        foreach ( $this->whereZusatz as $key => $value )
        {
            $paramWhere[] = $key . ' = ?';
        }
        $whereZusatz = implode ( ' AND ', $paramWhere );
        if ( ! empty( $whereZusatz ) )
        {
            $whereZusatz = '  AND  ' . $whereZusatz;
        }
        $paramOr = array ();
        if ( empty( $this->orZusatz ) )
        {
            $this->orZusatz = [ ];
        }
        foreach ( $this->orZusatz as $key => $value )
        {
            foreach ( $value as $val )
            {
                $paramOr[] = $key . ' != ?';
            }
        }
        $orZusatz = implode ( ' AND ', $paramOr );
        if ( ! empty( $orZusatz ) )
        {
            $orZusatz = '  AND  (' . $orZusatz . ')';
        }
        for (
            $i = 0; $i < count ( $this->columns ); $i++ )
        {
            $colArr[] = $this->columns[ $i ]['db'];
        }
        $columns = implode ( ', ', $colArr );
        $tables  = implode ( ' INNER JOIN ', $this->table );
        $sql = 'SELECT ' . $columns . ' FROM ' . $tables;
        $sql .= ' WHERE ';

        $sql .= $this->searchColumn ()['searchString'] . '  ' . $likeZusatz . '  ' . $whereZusatz . ' ' .
                $orZusatz . '  GROUP BY ' . $this->primaryKey;

        $this->searchParams = $this->searchColumn ()['params'];

        $stmtRowCount = $this->database->conn->prepare($sql);
        $stmtRowCount->execute($this->searchParams);
        $totalRows = $stmtRowCount->rowCount();
        return $totalRows;
    }
}