<?php
namespace Pronamic\Twinfield\Article\Mapper;

use \Pronamic\Twinfield\Article\Article;
use \Pronamic\Twinfield\Article\ArticleLine;
use \Pronamic\Twinfield\Response\Response;

/**
 * Maps a response DOMDocument to the corresponding entity.
 * 
 * @package Pronamic\Twinfield
 * @subpackage Mapper
 * @author Willem van de Sande <W.vandeSande@MailCoupon.nl>
 */
class ArticleMapper
{
    /**
     * Maps a Response object to a clean Article entity.
     * 
     * @access public
     * @param \Pronamic\Twinfield\Response\Response $response
     * @return \Pronamic\Twinfield\Article\Article
     */
    public static function map(Response $response)
    {
        // Generate new Article object
        $article = new Article();
        
        // Gets the raw DOMDocument response.
        $responseDOM = $response->getResponseDocument();
        
        // Set the status attribute
        $dimensionElement = $responseDOM->getElementsByTagName('header')->item(0);
        $article->setStatus($dimensionElement->getAttribute('status'));

        // Article elements and their methods
        $articleTags = array(
            'code'              => 'setCode',
            'office'            => 'setOffice',
            'type'              => 'setType',
            'name'              => 'setName',
            'shortname'         => 'setShortName',
            'unitnamesingular'  => 'setUnitNameSingular',
            'unitnameplural'    => 'setUnitNamePlural',
            'vatnumber'         => 'setVatNumber',
            'allowchangevatcode' => 'setAllowChangeVatCode',
            'performancetype'   => 'setPerformanceType',
            'allowchangeperformancetype' => 'setAllowChangePerformanceType',
            'percentage'        => 'setPercentage',
            'allowdiscountorpremium' => 'setAllowDiscountorPremium',
            'allowchangeunitsprice' => 'setAllowChangeUnitsPrice',
            'allowdecimalquantity' => 'setAllowDecimalQuantity',
            
        );
        
        // Loop through all the tags
        foreach($headerTags as $tag => $method) {
            
            // Get the dom element
            $_tag = $responseDOM->getElementsByTagName($tag)->item(0);

            // If it has a value, set it to the associated method
            if(isset($_tag) && isset($_tag->textContent))
                $article->$method($_tag->textContent);
        }

        $linesDOMTag = $responseDOM->getElementsByTagName('lines');            
        if (isset($linesDOMTag) && $linesDOMTag->length > 0) {
            
            $articleLine->setStatus($linesDOMTag->getAttribute('status'))
                    ->setInUse($linesDOMTag->getAttribute('inuse'));
        

            // Element tags and their methods for lines
            $lineTags = array(
                'unitspriceexcl'  => 'setUnitsPriceExcl',
                'unitspriceinc'   => 'setUnitsPriceInc',
                'units'           => 'setUnits',
                'name'            => 'setName',
                'shortname'       => 'setShortName',
                'subcode'         => 'setSubCode',
                'freetext1'       => 'setFreeText1',
            );

            $linesDOM = $linesDOMTag->item(0);

            // Loop through each returned line for the article
            foreach($linesDOM->getElementsByTagName('line') as $lineDOM) {

                // Make a new tempory ArticleLine class
                $temp_line = new ArticleLine();

                // Set the attributes ( id,status,inuse)
                $temp_line
                    ->setID($lineDOM->getAttribute('id'))
                    ->setStatus($lineDOM->getAttribute('status'))
                    ->setInUse($lineDOM->getAttribute('inuse'));

                // Loop through the element tags. Determine if it exists and set it if it does
                foreach($lineTags as $tag => $method) {

                    // Get the dom element
                    $_tag = $lineDOM->getElementsByTagName($tag)->item(0);

                    // Check if the tag is set, and its content is set, to prevent DOMNode errors
                    if(isset($_tag) && isset($_tag->textContent))
                        $temp_line->$method($_tag->textContent);
                }

                // Add the bank to the customer
                $article->addLine($temp_line);

                // Clean that memory!
                unset($temp_line);
            }
        }

        return $article;
    }
}
