<?php


function pager($data)
	{
		$data['leave_out']    = isset($data['leave_out']) ? $data['leave_out'] : '';
		$data['USE_ST']		  = isset($data['USE_ST'])	? $data['USE_ST']	 : '';
		$work = array( 'pages' => 0, 'page_span' => '', 'st_dots' => '', 'end_dots' => '' );
		
		$section = !$data['leave_out'] ? 2 : $data['leave_out'];  // Number of pages to show per section( either side of current), IE: 1 ... 4 5 [6] 7 8 ... 10
		
		$use_st  = !$data['USE_ST'] ? 'page' : $data['USE_ST'];
		
		
		if ( $data['TOTAL_POSS'] > 0 )
		{
			$work['pages'] = ceil( $data['TOTAL_POSS'] / $data['PER_PAGE'] );
		}
		
		$work['pages'] = $work['pages'] ? $work['pages'] : 1;
		
		
		$work['total_page']   = $work['pages'];
		$work['current_page'] = $data['CUR_ST_VAL'] > 0 ? ($data['CUR_ST_VAL'] / $data['PER_PAGE']) + 1 : 1;
		
		
		$previous_link = "";
		$next_link     = "";
		
		if ( $work['current_page'] > 1 )
		{
			$start = $data['CUR_ST_VAL'] - $data['PER_PAGE'];
			$previous_link = "<a href=\"{$data['BASE_URL']}&amp;$use_st=$start\" title=\"Previous\"><span style=\"background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">&lt;</span></a>";
		}
		
		if ( $work['current_page'] < $work['pages'] )
		{
			$start = $data['CUR_ST_VAL'] + $data['PER_PAGE'];
			$next_link = "&nbsp;<a href=\"{$data['BASE_URL']}&amp;$use_st=$start\" title=\"Next\"><span style=\"background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">&gt;</span></a>";
		}
		

		
		if ($work['pages'] > 1)
		{
			$work['first_page'] = "<span style=\"background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">{$work['pages']} Pages</span>&nbsp;";
			
			for( $i = 0; $i <= $work['pages'] - 1; ++$i )
			{
				$RealNo = $i * $data['PER_PAGE'];
				$PageNo = $i+1;
				
				if ($RealNo == $data['CUR_ST_VAL'])
				{
					$work['page_span'] .= "&nbsp;<span style=\"background: #FFC9A5; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">{$PageNo}</span>";
				}
				else
				{
					if ($PageNo < ($work['current_page'] - $section))
					{
						$work['st_dots'] = "<a href=\"{$data['BASE_URL']}\" title=\"Goto First\"><span style=\"background: #DFE6EF; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">&laquo;</span></a>&nbsp;";
						continue;
					}
					
					
					if ($PageNo > ($work['current_page'] + $section))
					{
						$work['end_dots'] = "&nbsp;<a href=\"{$data['BASE_URL']}&amp;$use_st=".(($work['pages']-1) * $data['PER_PAGE'])."\" title=\"Go To Last\"><span style=\"background: #DFE6EF; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">&raquo;</span></a>&nbsp;";
						break;
					}
					
					
					$work['page_span'] .= "&nbsp;<a href=\"{$data['BASE_URL']}&amp;$use_st={$RealNo}\" title=\"$PageNo\"><span style=\"background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">$PageNo</span></a>";
				}
			}
			
			$work['return'] = "<div align='center'>{$work['first_page']}{$work['st_dots']}{$previous_link}{$work['page_span']}{$next_link}{$work['end_dots']}
			</div>";
			
		}
		else
		{
			$work['return']    = $data['L_SINGLE'];
		}
	
		return $work['return'];
	}

?>