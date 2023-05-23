<?php
    class user_Class
    {
        private $table_name = 'user';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,email,password,accesslevel) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['email'])."','".
             $this->cleanData(md5($data['password']))."','".
             $this->cleanData($data['accesslevel'])."') RETURNING id";

            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {

							# insert user groups
							$values = array();
							foreach($data['groups'] as $group_id){
								array_push($values, "(".$row->id.",".$group_id.")");
							}

							$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
							$ret = pg_query($this->dbconn, $sql);

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

				function getRowssArr(){
						$rv = array();

						$sql = "select id,name from public.".$this->table_name;
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

        function getById($id){

            $sql ="select * from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

				function loginCheck($pwd, $email){
					$email = pg_escape_string($this->dbconn, $email);
					$hashpassword = md5($pwd);
	        $sql ="select * from public.user where email = '".$email."' and password ='".$hashpassword."'";
	        $res = pg_query($this->conn,$sql);
	        return pg_fetch_object($res);
				}

				function getUserAccessGroups($id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.user_access where user_id='".intval($id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

       function delete($id)
       {
					 $sql ="delete from public.user_access where user_id='".intval($id)."'";
					 $rv = pg_query($this->dbconn, $sql);

            $sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {

          $sql = "update public.user set name='".
          $this->cleanData($data['name'])."', email='".
          $this->cleanData($data['email'])."', password='".

          $this->cleanData($data['password'])."', accesslevel='".
          $this->cleanData($data['accesslevel'])."' where id = '".
          intval($data['id'])."' ";

					$rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old groups
						$sql = "delete from public.user_access where user_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert user groups
						$values = array();
						foreach($data['groups'] as $group_id){
							array_push($values, "(".$data['id'].",".$group_id.")");
						}

						$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}

					return $rv;
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
