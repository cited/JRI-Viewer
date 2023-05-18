<?php
    class tiles_Class
    {
        private $table_name = 'tiles';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."



             (url,repname,host,username,outname,name,description,schema,password,geom,database,tbl,owner) "."VALUES('".
             $this->cleanData($data['url'])."','".
             $this->cleanData($data['repname'])."','".
             $this->cleanData($data['host'])."','".
			 $this->cleanData($data['username'])."','".
             $this->cleanData($data['outname'])."','".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['description'])."','".
             $this->cleanData($data['schema'])."','".
             $this->cleanData($data['password'])."','".
             $this->cleanData($data['geom'])."','".
             $this->cleanData($data['database'])."','".
             $this->cleanData($data['tbl'])."','".
			 $this->cleanData($data['owner'])."') RETURNING id";
            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {
                return $row->id;
            }
            return 0;

            //return pg_affected_rows(pg_query($this->dbconn, $sql));
        }

        function getRows()
        {
            $sql ="select * from public." .$this->table_name . "
            ORDER BY id DESC";
           return pg_query($this->dbconn, $sql);
        }

        function getById($id){
            $sql ="select * from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

       function delete($id){
            $sql ="delete from public." .$this->table_name . "  
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {

          $sql = "update public.tiles set url='".
          $this->cleanData($data['url'])."', repname='".
          $this->cleanData($data['repname'])."', host='".
          $this->cleanData($data['host'])."', username='".
		  $this->cleanData($data['username'])."', outname='".
          $this->cleanData($data['outname'])."', name='".
          $this->cleanData($data['name'])."', description='".
          $this->cleanData($data['description'])."', schema='".
         $this->cleanData($data['schema'])."', password='".
         $this->cleanData($data['password'])."', geom='".
         $this->cleanData($data['geom'])."', database='".
         $this->cleanData($data['database'])."', tbl='".
         $this->cleanData($data['tbl'])."', owner='".
          $this->cleanData($data['owner'])."' where id = '".
          intval($data['id'])."'
          ";
          return pg_affected_rows(pg_query($this->dbconn, $sql));


       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
