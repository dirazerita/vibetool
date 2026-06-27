<?php

/**
 * Ok, glad you are here
 * first we get a config instance, and set the settings
 * $config = HTMLPurifier_Config::createDefault();
 * $config->set('Core.Encoding', $this->config->get('purifier.encoding'));
 * $config->set('Cache.SerializerPath', $this->config->get('purifier.cachePath'));
 * if ( ! $this->config->get('purifier.finalize')) {
 *     $config->autoFinalize = false;
 * }
 * $config->loadArray($this->getConfig());
 *
 * You must NOT delete the default settings
 * anything in settings should be compacted with params that needed to instance HTMLPurifier_Config.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */

return [
    'encoding' => 'UTF-8',
    'finalize' => true,
    'ignoreNonStrings' => false,
    'cachePath' => storage_path('app/purifier'),
    'cacheFileMode' => 0755,
    'settings' => [
        'default' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ],
        'landing_content' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'p[style],br,hr,'
                .'h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],'
                .'b,strong,i,em,u,s,mark,sub,sup,code,'
                .'ul,ol,li,'
                .'blockquote[style],pre,'
                .'a[href|title|target|rel],'
                .'img[src|alt|title|width|height|style],'
                .'span[style],div[style],'
                .'table[style],thead,tbody,tr[style],th[style|colspan|rowspan],td[style|colspan|rowspan]',
            'HTML.TargetBlank' => true,
            'HTML.Nofollow' => true,
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,font-variant,text-decoration,text-transform,'
                .'padding,padding-left,padding-right,padding-top,padding-bottom,'
                .'margin,margin-left,margin-right,margin-top,margin-bottom,'
                .'color,background,background-color,text-align,text-indent,line-height,letter-spacing,'
                .'border,border-color,border-width,border-style,border-top,border-right,border-bottom,border-left,'
                .'width,height,min-width,min-height,max-width,max-height,'
                .'list-style,list-style-type,list-style-position,'
                .'vertical-align,white-space',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true],
        ],
        'custom_html_content' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'p[style|class],br,hr,'
                .'h1[style|class],h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class],'
                .'b,strong,i,em,u,s,mark,sub,sup,code,'
                .'ul[style|class],ol[style|class],li[style|class],'
                .'blockquote[style|class],pre,'
                .'a[href|title|target|rel|style|class],'
                .'img[src|alt|title|width|height|style|class|loading],'
                .'span[style|class],div[style|class],'
                .'table[style|class],thead,tbody,tr[style|class],th[style|class|colspan|rowspan],td[style|class|colspan|rowspan],'
                .'section[style|class],article[style|class],header[style|class],footer[style|class],nav[style|class],main[style|class],aside[style|class],'
                .'figure[style|class],figcaption[style|class],'
                .'form[style|class|action|method|target],input[style|class|type|name|value|placeholder|required],'
                .'button[style|class|type],select[style|class|name],option[value|selected],textarea[style|class|name|rows|cols|placeholder],'
                .'label[style|class|for],fieldset[style|class],legend[style|class],'
                .'iframe[src|width|height|style|frameborder|allowfullscreen|loading],'
                .'video[src|width|height|style|controls|autoplay|loop|muted|poster],audio[src|style|controls],source[src|type|srcset],'
                .'picture,svg[style|class|width|height|viewBox|fill|stroke],path[d|fill|stroke|stroke-width],circle[cx|cy|r|fill],rect[width|height|x|y|fill],'
                .'details[style|class],summary[style|class]',
            'HTML.TargetBlank' => true,
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,font-variant,text-decoration,text-transform,'
                .'padding,padding-left,padding-right,padding-top,padding-bottom,'
                .'margin,margin-left,margin-right,margin-top,margin-bottom,'
                .'color,background,background-color,background-image,background-size,background-position,background-repeat,'
                .'text-align,text-indent,line-height,letter-spacing,word-spacing,'
                .'border,border-color,border-width,border-style,border-top,border-right,border-bottom,border-left,'
                .'border-radius,border-top-left-radius,border-top-right-radius,border-bottom-left-radius,border-bottom-right-radius,'
                .'width,height,min-width,min-height,max-width,max-height,'
                .'list-style,list-style-type,list-style-position,'
                .'vertical-align,white-space,overflow,overflow-x,overflow-y,'
                .'display,position,top,right,bottom,left,z-index,'
                .'opacity,visibility,box-shadow,text-shadow,transform,transition,'
                .'flex,flex-direction,flex-wrap,justify-content,align-items,align-content,gap,'
                .'grid-template-columns,grid-template-rows,grid-gap,grid-column-gap,grid-row-gap,'
                .'object-fit,object-position,cursor,pointer-events,user-select',
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|www\.google\.com/maps/embed\?)%',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => false,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true, 'data' => true],
        ],
        'test' => [
            'Attr.EnableID' => 'true',
        ],
        'youtube' => [
            'HTML.SafeIframe' => 'true',
            'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
        ],
        'custom_definition' => [
            'id' => 'html5-definitions',
            'rev' => 1,
            'debug' => false,
            'elements' => [
                // http://developers.whatwg.org/sections.html
                ['section', 'Block', 'Flow', 'Common'],
                ['nav',     'Block', 'Flow', 'Common'],
                ['article', 'Block', 'Flow', 'Common'],
                ['aside',   'Block', 'Flow', 'Common'],
                ['header',  'Block', 'Flow', 'Common'],
                ['footer',  'Block', 'Flow', 'Common'],

                // Content model actually excludes several tags, not modelled here
                ['address', 'Block', 'Flow', 'Common'],
                ['hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common'],

                // http://developers.whatwg.org/grouping-content.html
                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
                ['figcaption', 'Inline', 'Flow', 'Common'],

                // http://developers.whatwg.org/the-video-element.html#the-video-element
                ['video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                    'src' => 'URI',
                    'type' => 'Text',
                    'width' => 'Length',
                    'height' => 'Length',
                    'poster' => 'URI',
                    'preload' => 'Enum#auto,metadata,none',
                    'controls' => 'Bool',
                ]],
                ['source', 'Block', 'Flow', 'Common', [
                    'src' => 'URI',
                    'type' => 'Text',
                ]],

                // http://developers.whatwg.org/text-level-semantics.html
                ['s',    'Inline', 'Inline', 'Common'],
                ['var',  'Inline', 'Inline', 'Common'],
                ['sub',  'Inline', 'Inline', 'Common'],
                ['sup',  'Inline', 'Inline', 'Common'],
                ['mark', 'Inline', 'Inline', 'Common'],
                ['wbr',  'Inline', 'Empty', 'Core'],

                // http://developers.whatwg.org/edits.html
                ['ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
                ['del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
            ],
            'attributes' => [
                ['iframe', 'allowfullscreen', 'Bool'],
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],
        'custom_attributes' => [
            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
        ],
        'custom_elements' => [
            ['u', 'Inline', 'Inline', 'Common'],
        ],
    ],

];
