<?
namespace WD\Utilities\Export;

use
	\WD\Utilities\Helper,
	\WD\Utilities\Options;

Helper::loadMessages();

return [
	'OPTIONS' => [
		'fastsql_enabled' => [
			'CALLBACK_HEAD_DATA' => function($obOptions, $arOption, $strOption, $strOptionId){
				?>
				<script>
				$(document).delegate('#<?=$strOptionId;?>', 'change', function(e){
					$('#<?=$obOptions->getOptionPrefix('fastsql_auto_exec');?>').closest('tr').toggle($(this).prop('checked'));
				});
				$(document).ready(function(){
					$('#<?=$strOptionId;?>').trigger('change');
				});
				</script>
				<?
			},
		],
		'fastsql_auto_exec' => [
			'TYPE' => 'select',
			'VALUES' => [
				'N' => Options::getMessage('FASTSQL_AUTO_EXEC_N'),
				'Y' => Options::getMessage('FASTSQL_AUTO_EXEC_Y'),
				'X' => Options::getMessage('FASTSQL_AUTO_EXEC_X'),
			],
		],
	],
];
?>