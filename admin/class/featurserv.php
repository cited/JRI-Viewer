<?php
    class featurserv_Class
    {
        private $table_name = 'wms';
        private $dbconn = null;

        function __construct($dbconn) {
            $this->dbconn = $dbconn;
        }

        function create($data)
        {
             $sql = "INSERT INTO PUBLIC." .$this->table_name."
             (name,description,url,collection,cql,lat,lon,zoom,basemap,metric) "."VALUES('".
             $this->cleanData($data['name'])."','".
             $this->cleanData($data['description'])."','".
             $this->cleanData($data['url'])."','".
             $this->cleanData($data['collection'])."','".
	     $this->cleanData($data['cql'])."','".
             $this->cleanData($data['lat'])."','".
			 $this->cleanData($data['lon'])."','".
			 $this->cleanData($data['zoom'])."','".
			 $this->cleanData($data['basemap'])."','".
                         $this->cleanData($data['metric']).") RETURNING id";

            $row = pg_fetch_object(pg_query($this->dbconn, $sql));

            if($row) {
								# insert into access groups
								$values = array();
								foreach($data['accgrps'] as $group_id){
									array_push($values, "(".$group_id.",".$row->id.")");
								}

								$sql = "insert into public.group_access (access_group_id,wms_id) values ".implode(',', $values);
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

						$sql ="select id,name from public.access_groups WHERE id in (SELECT access_group_id from public.wms_access where wms_id='".intval($id)."')";
						$result = pg_query($this->dbconn, $sql);

						while ($row = pg_fetch_assoc($result)) {
							$rv[$row['id']] = $row['name'];
						}
						return $rv;
				}

       function delete($id)
       {

						$sql ="delete from public.wms_access where wms_id='".intval($id)."'";
						$rv = pg_query($this->dbconn, $sql);

            $sql ="delete from public." .$this->table_name . "
            where id='".intval($id)."'";
            return pg_query($this->dbconn, $sql);
       }

       function update($data=array())
       {

          $sql = "update public.wms set name='".
          $this->cleanData($data['name'])."', description='".
          $this->cleanData($data['description'])."', url='".
          $this->cleanData($data['url'])."', collection='".
          $this->cleanData($data['collection'])."', cql='".
          $this->cleanData($data['cql'])."', lat='".
          $this->cleanData($data['lat'])."', lon='".
	  $this->cleanData($data['lon'])."', zoom='".
          $this->cleanData($data['zoom'])."', basemap='".
          $this->cleanData($data['basemap'])."', metric='".
          $this->cleanData($data['metric'])."' where id = '".
          intval($data['id'])."' ";

					$rv = pg_affected_rows(pg_query($this->dbconn, $sql));

					if($rv > 0){
						# drop old access groups
						$sql = "delete from public.wms_access where wms_id=".$data['id'];
						$ret = pg_query($this->dbconn, $sql);

						# insert access groups
						$values = array();
						foreach($data['accgrps'] as $group_id){
							array_push($values, "(".$group_id.",".$data['id'].")");
						}

						$sql = "insert into public.wms_access (access_group_id,wms_id) values ".implode(',', $values);
						$ret = pg_query($this->dbconn, $sql);
					}
       }

       function cleanData($val)
       {
         return pg_escape_string($this->dbconn, $val);
       }
	}
