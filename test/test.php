<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<style>
    body{
        margin:100px;
    }
</style>
<meta charset="utf-8">
<?php
    /**
     * Just quickly load all Classes by using an autoloader
     *
     * @param $class_name
     */
    function __autoload ( $class_name )
    {
        include '../' . $class_name . '.php';
    }

    /**
     * When you want to join multiple tables to your view, just put them in an array.
     * Default is "INNER JOIN"
     *
     * IF you wish a different join, you can join them "manually"
     * e.g.
     * $tablename = ['users', 'customers LEFT JOIN customers_phonenumbers as c_phone ON cusomers.id = c_phone.id'];
     *
     * @type [array]
     */
    $tablename = [ 'users' ];
    /**
     * The array consists of many arrays.
     * There always needs to be a 'db' and a 'name' key in it.
     *
     * the 'db' value represents the columnname in the database
     * while the 'name' value is the Columntitle in the rendered Table
     *
     * @type [array]
     */
    $columns
        = [
        [ 'db' => 'usr_id', 'name' => 'ID' ],
        [ 'db' => 'usr_username', 'name' => 'Username' ],
        [ 'db' => 'usr_first_name', 'name' => 'First Name' ],
        [ 'db' => 'usr_last_name', 'name' => 'Last Name' ]
    ];
    /**
     * The table is always sorted by $primaryKey ascending by default.
     *
     * @type = string
     */
    $primaryKey = 'usr_id';
    /**
     * every row has a detail page you get redirected to when you click on the button in the first column
     * eg. $detailPage = 'user';
     * redirects to 'user/5';
     *
     * eg. $detailPage = 'http://google.com';
     * redirects to 'http://google.com/5';
     *
     *
     * @type string
     */
    $detailPage  = 'user';
    /**
     * add multiple "WHERE/AND  abc = def" to your query
     *
     * e.g. $whereZusatz = ['abc' => 'def']
     *
     * @type [array]
     */
    $whereZusatz = [ ];
    /**
     * add multiple "WHERE/AND  abc LIKE %def%" to your query
     *
     * e.g. $likeZusatz = ['abc' => 'def']
     *
     * @type [array]
     */
    $likeZusatz  = [ ];
    /**
     * rules: asc, desc
     *
     * add a rule to change sortorder
     *
     * @type string
     */
    $rule        = '';
    /**
     * add multiple "WHERE/OR  abc LIKE %def%" to your query
     *
     * e.g. $orZusatz = ['abc' => 'def']
     *
     * @type [array]
     */
    $orZusatz    = [ ];
    /**
     * load TableGenerator
     * requires all of the given parameters
     *
     */
    $table = new Table( $tablename, $primaryKey, $columns, $detailPage, $whereZusatz, $likeZusatz, $rule, $orZusatz );
    /**
     * Build the table in HTML.
     */
    $table = $table->build ();
    /**
     * Echo the table
     */
    echo $table;
?>