<?php
/**
 * @license GPL-2.0-only
 *
 * Modified by learndash on 02-October-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace LearnDash\Certificate_Builder\Mpdf\Tag;

class TocPageBreak extends FormFeed
{
	public function open($attr, &$ahtml, &$ihtml)
	{
		list($isbreak, $toc_id) = $this->tableOfContents->openTagTOCPAGEBREAK($attr);
		$this->toc_id = $toc_id;
		if ($isbreak) {
			return;
		}
		parent::open($attr, $ahtml, $ihtml);
	}
}
