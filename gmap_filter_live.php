<?php
if(!class_exists('FILTERREQUEST')){
	class FILTERREQUEST{
		private $query_string, $props, $where;
		public function __construct( $args = array() ){
			$this->props = $args;
			$this->query_string = $this->clean_first_request( $args );
		}

		public function clean_first_request( $req ){
			$req = explode(',', $req['query']);
			$clean = array_map('trim', $req);
			return implode(',', $clean);
		}

		public function conditions( $args, $where = '' ){

			switch( (string)$args[0] ){
				
				//price range
				case 'price_range':
					
					$price_range = explode('::', $args[1]);
					$where = "AND Lp_dol >= {$price_range[0]} AND Lp_dol <= {$price_range[1]}";

					break;

				//bedroom
				case 'bedroom':
					if( $args[1] > 0 )
					$where = "AND Br = {$args[1]}";
					break;

				//property type
				case 'prop_type':
					$PROP = $this->getPropType( $this->props['table'] );
					$props = explode( '|', $args[1]);
					$pt = array_filter($props);
					if( count($pt) > 1 ){
                        $c = 1; $OR = 'OR';
                        foreach( $pt as $p ){
                            if($c == 1){
                                $where_prop .= "AND (";
                            }

                            if($c == count($pt))
                                $OR = '';
                            
                            $where_prop .= str_replace(' as type_of_home', '', $PROP) . " = '$p' $OR ";
                            
                            if($c == count($pt)){
                                $where_prop .= ")";
                            }

                            $c++;
                        }
                    }else{
                        $where_prop = "AND " . str_replace(' as type_of_home', '', $PROP) . " = '" . str_replace('|', '', $args[1]) . "'";
                    }

                    $where = $where_prop;
					break;

			}
			return $where;
		}

		public function prepare_conditions(){
			foreach( $this->props as $args => $data ){
				if( $args != 'query' && $args != 'table' && $data != '' ){
					$this->where .= $this->conditions( array( $args, $data ) ) . ' ';
				}
			}
		}

		public function residential_condo_statement(){
			return   "SELECT * FROM(
					  SELECT DISTINCT Addr, Ml_num,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Type_own1_out as type_of_home, 'rets_property_residentialproperty' as prop_type 
					  FROM rets_property_residentialproperty
					  WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip) LIKE '%{$this->query_string}%'
					  {$this->where}
					  UNION
					  SELECT DISTINCT Addr, Ml_num,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Type_own1_out as type_of_home, 'rets_property_condoproperty' as prop_type
					  FROM rets_property_condoproperty
					  WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip) LIKE '%{$this->query_string}%'
					  {$this->where}) as property
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";
		}

		public function residential_statement(){
			return   "SELECT DISTINCT Addr, Ml_num,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Type_own1_out as type_of_home, 'rets_property_residentialproperty' as prop_type 
					  FROM rets_property_residentialproperty
					  WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip) LIKE '%{$this->query_string}%'
					  {$this->where}
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";
		}

		public function condo_statement(){
			return   "SELECT DISTINCT Addr, Ml_num,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Br, Type_own1_out as type_of_home, 'rets_property_condoproperty' as prop_type
					  FROM rets_property_condoproperty
					  WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip) LIKE '%{$this->query_string}%'
					  {$this->where}
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";
		}

		public function commercial_statement(){
			return   "SELECT DISTINCT Addr, Ml_num,Zip, Ad_text,Municipality,County,Area,Timestamp_sql,Lp_dol,Prop_type as type_of_home, 'rets_property_commercialproperty' as prop_type 
					  FROM rets_property_commercialproperty
					  WHERE CONCAT_WS(',', Community,Municipality,Ml_num,Zip) LIKE '%{$this->query_string}%'
					  {$this->where}
					  GROUP BY CONCAT(Addr,' ',Municipality,' ',County)
					  ORDER BY Timestamp_sql DESC";
		}

		public function working_sql( $statement = '' ){
			
			$this->prepare_conditions();
			
			$statements = array(
							'residential_condo' => $this->residential_condo_statement(),
							'residential' 		=> $this->residential_statement(),
							'condo' 			=> $this->condo_statement(),
							'commercial' 		=> $this->commercial_statement()
						  );
			
			$this->create_cache_file( $statements[ $this->props['table'] ] );
			
			return $statements[ $this->props['table'] ];
		}

		public function create_cache_file( $statement = '' ){
			file_put_contents(dirname( __FILE__ ) . '/query/storage/storage_data_query_xyz.txt', $statement);
		}

		public function getPropType( $prop ){
	        switch( $prop ){
	            case 'commercial':    						return 'Prop_type as type_of_home';        break;
	            case 'residential_condo':	   				return 'Type_own1_out as type_of_home';    break;
	        }
	    }
	}
}