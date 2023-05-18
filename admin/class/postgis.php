<?php
    class postgis_Class
    {
        private $table_name = 'pguser';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,description,host,database,schema,username,password,geom,metric,tbl,basebmap,lat,lon,zoom,pgtileurl) "."VALUES('".
             $this->cleanpgData($data['name'])."','".
             $this->cleanpgData($data['description'])."','".
             $this->cleanpgData($data['host'])."','".
             $this->cleanpgData($data['database'])."','".
			 $this->cleanpgData($data['schema'])."','".
             $this->cleanpgData($data['username'])."','".
             $this->cleanpgData($data['password'])."','".
			 $this->cleanpgData($data['geom'])."','".
			 $this->cleanpgData($data['metric'])."','".
             $this->cleanpgData($data['tbl'])."','".
             $this->cleanpgData($data['basemap'])."','".
             $this->cleanpgData($data['lat'])."','".
			 $this->cleanpgData($data['lon'])."','".
                         $this->cleanpgData($data['zoom'])."','".
                         $this->cleanpgData($data['pgtileurl'])."') RETURNING id";
            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {
								# insert into access groups
								$values = array();
								foreach($data['accgrps'] as $group_id){
									array_push($values, "(".$group_id.",".$row->id.")");
								}

								$sql = "insert into public.pguser_access (access_group_id,pguser_id) values ".implode(',', $values);
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

        function getById($id){

            $sql ="select * from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
        }

				function getGrpAccessGroups($id){
						$rv = array();

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.pguser_access where pguser_id='".intval($id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						return $rv;
				}

       function delete($id)
       {
				 		$sql ="delete from public.pguser_access where wms_id='".intval($id)."'";
						$rv = pg_query($this->dbconn, $sql);

            $sql ="delete from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {
          $sql = "update public.pguser set name='".
          $this->cleanpgData($data['name'])."', description='".
          $this->cleanpgData($data['description'])."', host='".
          $this->cleanpgData($data['host'])."', database='".
          $this->cleanpgData($data['database'])."', schema='".
          $this->cleanpgData($data['schema'])."', username='".
          $this->cleanpgData($data['username'])."', password='".
          $this->cleanpgData($data['password'])."', geom='".
          $this->cleanpgData($data['geom'])."', metric='".
          $this->cleanpgData($data['metric'])."', tbl='".
          $this->cleanpgData($data['tbl'])."', basemap='".
          $this->cleanpgData($data['basemap'])."', lat='".
          $this->cleanpgData($data['lat'])."', lon='".
          $this->cleanpgData($data['lon'])."', zoom='".
          $this->cleanpgData($data['zoom'])."', pgtileurl='".
          $this->cleanpgData($data['pgtileurl'])."' where id = '".
          intval($data['id'])."' ";

					$rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old access groups
						$sql = "delete from public.pguser_access where pguser_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert access groups
						$values = array();
						foreach($data['accgrps'] as $group_id){
							array_push($values, "(".$group_id.",".$data['id'].")");
						}

						$sql = "insert into public.pguser_access (access_group_id,pguser_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}
       }

       function cleanpgData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
