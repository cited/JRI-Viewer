<?php
    class access_group_Class
    {
        private $table_name = 'access_groups';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
            $sql = "INSERT INTO PUBLIC." .$this->table_name." (name) "."VALUES('".$this->cleanData($data['name'])."')";
            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {
							# insert report access
							$values = array();
							foreach($data['userids'] as $user_id){
								array_push($values, "(".$user_id.",".$row->id.")");
							}

							$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
							$ret = pg_query($this->dbconn, $sql);

              return $row->id;
            }
            return 0;
        }

        function getAccessGroups()
        {
            $sql = "select * from public." .$this->table_name . " ORDER BY id DESC";
           return pg_query($this->dbconn, $sql);
        }

				function getAccessGroupsArr(){
						$rv = array();

						$sql = "select id,name from public.".$this->table_name;
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGroupUsers($gids){
						$rv = array();

						$sql = "select id,name from public.user WHERE id in (select user_id from public.user_access where access_group_id in (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGroupReports($gids){
						$rv = array();

						$sql = "select id,name from public.jasper WHERE id in (select report_id from public.report_access where access_group_id in (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

				function getGroupReportGroups($gids){
						$rv = array();

						$sql = "select id,name from public.groups WHERE id in (SELECT report_group_id from public.group_access where access_group_id IN (".implode(',', $gids)."))";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
            return $rv;
        }

        function getGroupById($id){
            $sql ="select * from public." .$this->table_name . " where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

       function delete($id){

				 $sql ="delete from public.user_access where access_group_id='".intval($id)."'";
				 $rv = pg_query($this->dbconn, $sql);

				 $sql ="delete from public." .$this->table_name . " where id='".intval($id)."'";
				 return pg_query($this->dbconn, $sql);
       }

       function update($data=array()) {
          $sql = "update public.access_groups set name='".$this->cleanData($data['name'])."' where id = '".intval($data['id'])."' ";
          $rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){

						$sql ="delete from public.user_access where access_group_id='".intval($data['id'])."'";
	 				 	$rv = pg_query($this->dbconn, $sql);

						# insert report access
						$values = array();

						foreach($data['userids'] as $user_id){
							array_push($values, "(".$user_id.",".$data['id'].")");
						}

						$sql = "insert into public.user_access (user_id,access_group_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
