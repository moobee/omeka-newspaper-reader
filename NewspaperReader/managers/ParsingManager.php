<?php

/**
 * 	BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File ParsingManager.php : parsing the Alto files and generating the HTML layout
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 30/01/2014 - Loading then parsing the Alto file - VKIFFER
 *                  1.1 - 31/01/2014 - Generating the HTML layout - VKIFFER
 *					1.2 - 03/02/2014 - Optimization of the layout's generation  (resolution) - VKIFFER
 *
 *   Version : 1.2
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * ParsingManager class.
 *
 * @package NewspaperReader
 */
class ParsingManager
{
	/**
     * Parses the Alto file and returns its content
     *
     * @param $path The path of the file to be parsed
     * @return $content The content of the file
     */
	public function parseAltoFile($path)
	{
		// The file exists and is readable
		if(file_exists($path) && is_readable($path))
		{
			// Interprets the xml file as an object
    		$xmlFile = simplexml_load_file($path);

    		// Retrieves the different styles of the document's text
    		$styles = $xmlFile->Styles;

    		// Array used to store the styles of the document
    		$textStyles = array();

    		// Browses document's styles : fontfamily, fontsize, fontstyle and fontcolor
    		foreach ($styles->TextStyle as $textStyle)
    		{
    			if($textStyle['FONTFAMILY'])
    			{
    				// Adds the string's fontfamily to the styles array
    				$textStyles[(string)$textStyle['ID']]['FONTFAMILY'] = (string)$textStyle['FONTFAMILY'];
    			}

    			if($textStyle['FONTSIZE'])
    			{
    				// Adds the string's fontsize to the styles array
    				$textStyles[(string)$textStyle['ID']]['FONTSIZE'] = (string)$textStyle['FONTSIZE'] ;
    			}

    			if($textStyle['FONTSTYLE'])
    			{
    				// Adds the string's fontstyle to the styles array
    				$textStyles[(string)$textStyle['ID']]['FONTSTYLE'] = (string)$textStyle['FONTSTYLE'];
    			}

    			if($textStyle['FONTCOLOR'])
    			{
    				// Adds the string's fontcolor to the styles array
    				$textStyles[(string)$textStyle['ID']]['FONTCOLOR'] = (string)$textStyle['FONTCOLOR'];
    			}
    		}

    		// Retrieves the different elements of the document's text
    		$layout = $xmlFile->Layout;

    		// Array used to store the content of the document
    		$textContent = array();

    		/* Browses the file's tree to retrieve its strings
    		*	LAYOUT
    		*	  PAGE
    		*	    PRINTSPACE
    		*		  TEXTBLOCK
    		*			TEXTLINE
    		*			  STRING
    		*		  COMPOSEDBLOCK
    		*			TEXTBLOCK
    		*			  TEXTLINE
    		*				STRING
    		*/
    		foreach ($layout->Page as $page)
    		{
    			foreach ($page->PrintSpace as $printSpace)
    			{
    				foreach ($printSpace->TextBlock as $textBlock)
    				{
    					foreach ($textBlock->TextLine as $textLine)
    					{
    						foreach ($textLine->String as $string)
	    					{
	    						// Adds the string and its infos to the content
	    						$textContent = $this->fillContent($textContent, $textStyles, $string, $textLine, $textBlock);
	    					}
    					}
    				}

    				foreach ($printSpace->ComposedBlock as $composedBlock)
    				{
    					foreach ($composedBlock->TextBlock as $textBlock)
    					{
    						foreach ($textBlock->TextLine as $textLine)
	    					{
	    						foreach ($textLine->String as $string)
		    					{
		    						// Adds the string and its infos to the content
		    						$textContent = $this->fillContent($textContent, $textStyles, $string, $textLine, $textBlock);
		    					}
	    					}
    					}
    				}
    			}
    		}

    		return $textContent;
		}

		// Error while attempting to open the file
		else
		{
		    return 'Failed to open '.$path;
		}
	}

	/**
     * Fills a content array with a string and its infos
     *
     * @param $content The content array
     * @param $styles The styles array
     * @param $string The string to be added
     * @param $textLine The line including the string
     * @param $textBlock The block including the string
     * @return $content The content array filled with the string and its infos
     */
	public function fillContent($content, $styles, $string, $textLine, $textBlock)
	{
		// if($string['WC'] > 0.75)
		// {
		// if(strcmp((string)$string['WD'], 'true') == 0 )
		// {
			// Adds the block, line, string, vpos, hpos, width and height to the content array
			$content[(string)$string['ID']]['Block'] = (string)$textBlock['ID'];
			$content[(string)$string['ID']]['Line'] = (string)$textLine['ID'];
			$content[(string)$string['ID']]['Content'] = (string)$string['CONTENT'];
			$content[(string)$string['ID']]['Vpos'] = (string)$string['VPOS'];
			$content[(string)$string['ID']]['Hpos'] = (string)$string['HPOS'];
			$content[(string)$string['ID']]['Width'] = (string)$string['WIDTH'];
			$content[(string)$string['ID']]['Height'] = (string)$string['HEIGHT'];


			// Checks if the string has a style associated with it in the styles array
			if(array_key_exists((string)$string['STYLEREFS'], $styles))
			{
				// Adds the style to the content array
				$content[(string)$string['ID']]['Style'] = $styles[(string)$string['STYLEREFS']];
			}
		// }

		return $content;
	}

	/**
     * Generates the pageobject according to the content and the view object of the current page
     *
     * @param $content The content array corresponding to the current page
     * @param $view The view object corresponding to the current page
     * @return $pageobject The pageobject generated
     */
	public function generatePageobject($content, $view)
	{
		$pageobject = new Pageobject();
		$pageobject->setView($view);
		$data = array();
		$i = 0;

		if (!is_null($content)) {
			foreach ($content as $word)
			{
				if(isset($word['Hpos']))
				{
					$data['words'][$i]['x'] = $word['Hpos'];
				}

				if(isset($word['Vpos']))
				{
					$data['words'][$i]['y'] = $word['Vpos'];
				}

				if(isset($word['Width']))
				{
					$data['words'][$i]['w'] = $word['Width'];
				}

				if(isset($word['Height']))
				{
					$data['words'][$i]['h'] = $word['Height'];
				}

				if(isset($word['Content']))
				{
					$data['words'][$i]['content'] = $word['Content'];
				}

				$i++;
			}
		}

	    $pageobject->setData($data);

		return $pageobject;
	}


	/**
     * Generates the HTML layout for a content array
     *
     * @param $content The content array
     * @param $reductionRate The reduction rate of the document
     * @return $layout The generated layout
     */
	public function generateLayout($content, $reductionRate)
	{

		// Si l'alto est vide, le content est null, pas de layout
		if (is_null($content)) {
			return '<p></p>';
		}

		$layout = '<p>';
		$previousBlock = null;
		$previousLine = null;

		foreach ($content as $string)
		{
			if($string['Line'] != $previousLine && $previousLine)
			{
				$layout .= '</br>';
			}

			$previousLine = $string['Line'];

			if($string['Block'] != $previousBlock && $previousBlock)
			{
				$layout .= '</p><p>';
			}

			$previousBlock = $string['Block'];

			// $layout .= '<span style="';

			// if($string['Style']['FONTFAMILY'])
			// {
			// 	$layout .= 'font-family: '.strtolower($string['Style']['FONTFAMILY']).'; ';
			// }

			if(isset($string['Style']))
			{
				switch ($string['Style']['FONTSIZE'])
				{
					// case ($string['Style']['FONTSIZE'] > (30 / $reductionRate)):
					// 	$layout .= '<h1>'.$string['Content'].' '.'</h1>';
					// 	break;

					default:
						$layout .= $string['Content'];
						$layout .= ' ';
						break;
				}

				// $layout .= 'font-size: '.strtolower($string['Style']['FONTSIZE']).'px; ';
			}

			// if($string['Style']['FONTSTYLE'])
			// {
			// 	$layout .= 'font-style: '.strtolower($string['Style']['FONTSTYLE']).'; ';
			// }

			// if($string['Style']['FONTCOLOR'])
			// {
			// 	$layout .= 'font-color: '.strtolower($string['Style']['FONTCOLOR']).'; ';
			// }

			// $layout .= '">';

			// $layout .= '</span>';
		}

		return $layout;
	}

	/**
     * Parses the Csv file and returns its content
     *
     * @param $path The path of the file to be parsed
     * @param $fieldDelimiterThe delimiter between two fields on the same line
     * @return $content The content of the file
     */
	public function parseCsvFile($path, $fieldDelimiter)
	{
		// The file exists and is readable
		if(file_exists($path) && is_readable($path))
		{
			// Header line of the file
			$header = NULL;

			// Content of the file
			$content = array();

			// Opens the filestream in read only mode
		    if (($handle = fopen($path, 'r')) !== FALSE)
		    {
		    	// Retrieves the lines of the file
		        while (($row = fgetcsv($handle, 1000, $fieldDelimiter)) !== FALSE)
		        {
		        	// Header line
		            if(!$header)
		            {
		                $header = $row;
		            }
		            // Data line
		            else
		            {
		            	// A row matches every header column
		            	if(count($header) == count($row))
		            	{
		            		// Adds the row to the content array
		            		$content[] = array_combine($header, $row);
		            	}
		            	// A row didn't match every header column
		            	else
		            	{
		            		return 'Parse error, the csv file isn\'t well formed' ;
		            	}
		            }
		        }
		        // Closes the filestream
		        fclose($handle);
		    }

			return $content;
		}

		// Error while attempting to open the file
		else
		{
		    return 'Failed to open '.$path;
		}
	}
}
