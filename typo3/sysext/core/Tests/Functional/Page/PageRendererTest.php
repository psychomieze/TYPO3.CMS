<?php
namespace TYPO3\CMS\Core\Tests\Functional\Page;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Test case
 */
class PageRendererTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    /**
     * @test
     */
    public function pageRendererRendersInsertsMainContentStringsInOutput()
    {
        $subject = new PageRenderer();
        $subject->setCharSet('utf-8');
        $subject->setLanguage('default');

        $prologueString = $expectedPrologueString = '<?xml version="1.0" encoding="utf-8" ?>';
        $subject->setXmlPrologAndDocType($prologueString);

        $title = $this->getUniqueId('aTitle-');
        $subject->setTitle($title);
        $expectedTitleString = '<title>' . $title . '</title>';

        $charset = 'utf-8';
        $subject->setCharSet($charset);
        $expectedCharsetString = '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '" />';

        $favouriteIcon = 'http://google.com/favicon.ico';
        $subject->setFavIcon($favouriteIcon);
        $expectedFavouriteIconPartOne = '<link rel="shortcut icon" href="' . $favouriteIcon . '" />';

        $baseUrl = 'http://google.com/';
        $subject->setBaseUrl($baseUrl);
        $expectedBaseUrlString = '<base href="' . $baseUrl . '" />';

        $subject->setMetaTag('property', 'og:type', 'foobar');
        $subject->setMetaTag('name', 'author', 'husel');
        $subject->setMetaTag('name', 'author', 'foobar');
        $subject->setMetaTag('http-equiv', 'refresh', '5');
        $subject->setMetaTag('name', 'DC.Author', '<evil tag>');
        $subject->setMetaTag('property', 'og:image', '/path/to/image1.jpg', [], false);
        $subject->setMetaTag('property', 'og:image', '/path/to/image2.jpg', [], false);

        // Unset meta tag
        $subject->setMetaTag('NaMe', 'randomTag', 'foobar');
        $subject->removeMetaTag('name', 'RanDoMtAg');

        $inlineComment = $this->getUniqueId('comment');
        $subject->addInlineComment($inlineComment);
        $expectedInlineCommentString = '<!-- ' . LF . $inlineComment . '-->';

        $headerData = $expectedHeaderData = '<tag method="private" name="test" />';
        $subject->addHeaderData($headerData);

        $subject->addJsLibrary('test', 'fileadmin/test.js', 'text/javascript', false, false, 'wrapBeforeXwrapAfter', false, 'X');
        $expectedJsLibraryRegExp = '#wrapBefore<script src="fileadmin/test\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>wrapAfter#';

        $subject->addJsFile('fileadmin/test.js', 'text/javascript', false, false, 'wrapBeforeXwrapAfter', false, 'X');
        $expectedJsFileRegExp = '#wrapBefore<script src="fileadmin/test\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>wrapAfter#';

        $jsInlineCode = $expectedJsInlineCodeString = 'var x = "' . $this->getUniqueId('jsInline-') . '"';
        $subject->addJsInlineCode($this->getUniqueId(), $jsInlineCode);

        $cssFile = $this->getUniqueId('cssFile-');
        $expectedCssFileString = 'wrapBefore<link rel="stylesheet" type="text/css" href="' . $cssFile . '" media="print" />wrapAfter';
        $subject->addCssFile($cssFile, 'stylesheet', 'print', '', true, false, 'wrapBeforeXwrapAfter', false, 'X');

        $expectedCssInlineBlockOnTopString = '/*general3*/' . LF . 'h1 {margin:20px;}' . LF . '/*general2*/' . LF . 'body {margin:20px;}';
        $subject->addCssInlineBlock('general2', 'body {margin:20px;}');
        $subject->addCssInlineBlock('general3', 'h1 {margin:20px;}', null, true);

        $subject->loadJquery();
        $expectedJqueryRegExp = '#<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery\\.min\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>#';

        $expectedBodyContent = $this->getUniqueId('ABCDE-');
        $subject->setBodyContent($expectedBodyContent);

        $renderedString = $subject->render();

        $this->assertContains($expectedPrologueString, $renderedString);
        $this->assertContains($expectedTitleString, $renderedString);
        $this->assertContains($expectedCharsetString, $renderedString);
        $this->assertContains($expectedFavouriteIconPartOne, $renderedString);
        $this->assertContains($expectedBaseUrlString, $renderedString);
        $this->assertContains($expectedInlineCommentString, $renderedString);
        $this->assertContains($expectedHeaderData, $renderedString);
        $this->assertRegExp($expectedJsLibraryRegExp, $renderedString);
        $this->assertRegExp($expectedJsFileRegExp, $renderedString);
        $this->assertContains($expectedJsInlineCodeString, $renderedString);
        $this->assertContains($expectedCssFileString, $renderedString);
        $this->assertContains($expectedCssInlineBlockOnTopString, $renderedString);
        $this->assertRegExp($expectedJqueryRegExp, $renderedString);
        $this->assertContains($expectedBodyContent, $renderedString);
        $this->assertContains('<meta property="og:type" content="foobar" />', $renderedString);
        $this->assertContains('<meta name="author" content="foobar" />', $renderedString);
        $this->assertContains('<meta http-equiv="refresh" content="5" />', $renderedString);
        $this->assertContains('<meta name="dc.author" content="&lt;evil tag&gt;" />', $renderedString);
        $this->assertNotContains('<meta name="randomtag" content="foobar">', $renderedString);
        $this->assertNotContains('<meta name="randomtag" content="foobar" />', $renderedString);
        $this->assertContains('<meta name="generator" content="TYPO3 CMS" />', $renderedString);
        $this->assertContains('<meta property="og:image" content="/path/to/image1.jpg" />', $renderedString);
        $this->assertContains('<meta property="og:image" content="/path/to/image2.jpg" />', $renderedString);
    }

    /**
     * @test
     */
    public function pageRendererRendersFooterValues()
    {
        $subject = new PageRenderer();
        $subject->setCharSet('utf-8');
        $subject->setLanguage('default');

        $subject->enableMoveJsFromHeaderToFooter();

        $footerData = $expectedFooterData = '<tag method="private" name="test" />';
        $subject->addFooterData($footerData);

        $expectedJsFooterLibraryRegExp = '#wrapBefore<script src="fileadmin/test\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>wrapAfter#';
        $subject->addJsFooterLibrary('test', 'fileadmin/test.js', 'text/javascript', false, false, 'wrapBeforeXwrapAfter', false, 'X');

        $expectedJsFooterRegExp = '#wrapBefore<script src="fileadmin/test\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>wrapAfter#';
        $subject->addJsFooterFile('fileadmin/test.js', 'text/javascript', false, false, 'wrapBeforeXwrapAfter', false, 'X');

        $jsFooterInlineCode = $expectedJsFooterInlineCodeString = 'var x = "' . $this->getUniqueId('jsFooterInline-') . '"';
        $subject->addJsFooterInlineCode($this->getUniqueId(), $jsFooterInlineCode);

        // Bunch of label tests
        $subject->addInlineLanguageLabel('myKey', 'myValue');
        $subject->addInlineLanguageLabelArray([
            'myKeyArray1' => 'myValueArray1',
            'myKeyArray2' => 'myValueArray2'
        ]);
        $subject->addInlineLanguageLabelArray([
            'myKeyArray3' => 'myValueArray3'
        ]);
        $expectedInlineLabelReturnValue = 'TYPO3.lang = {"myKey":"myValue","myKeyArray1":"myValueArray1","myKeyArray2":"myValueArray2","myKeyArray3":"myValueArray3"';

        $subject->addInlineLanguageLabelFile('EXT:core/Resources/Private/Language/locallang_core.xlf');
        $expectedLanguageLabel1 = 'labels.beUser';
        $expectedLanguageLabel2 = 'labels.feUser';

        // Bunch of inline settings test
        $subject->addInlineSetting('myApp', 'myKey', 'myValue');
        $subject->addInlineSettingArray('myApp', [
            'myKey1' => 'myValue1',
            'myKey2' => 'myValue2'
        ]);
        $subject->addInlineSettingArray('myApp', [
            'myKey3' => 'myValue3'
        ]);
        $expectedInlineSettingsReturnValue = 'TYPO3.settings = {"myApp":{"myKey":"myValue","myKey1":"myValue1","myKey2":"myValue2","myKey3":"myValue3"}';

        $renderedString = $subject->render(PageRenderer::PART_FOOTER);

        $this->assertContains($expectedFooterData, $renderedString);
        $this->assertRegExp($expectedJsFooterLibraryRegExp, $renderedString);
        $this->assertRegExp($expectedJsFooterRegExp, $renderedString);
        $this->assertContains($expectedJsFooterInlineCodeString, $renderedString);
        $this->assertContains($expectedInlineLabelReturnValue, $renderedString);
        $this->assertContains($expectedLanguageLabel1, $renderedString);
        $this->assertContains($expectedLanguageLabel2, $renderedString);
        $this->assertContains($expectedInlineSettingsReturnValue, $renderedString);
    }

    /**
     * @return array
     */
    public function jqueryNamespaceDataProvider()
    {
        return [
            'Custom namespace falls back to "no conflict"' => [
                'namespace' => 'MyNameSpace',
                'matchNoConflict' => true,
            ],
            '"None" namespace is respected' => [
                'namespace' => PageRenderer::JQUERY_NAMESPACE_NONE,
                'matchNoConflict' => false,
            ],
        ];
    }

    /**
     * @param string $namespace
     * @param bool $matchNoConflict
     * @test
     * @dataProvider jqueryNamespaceDataProvider
     */
    public function loadJqueryRespectsNamespace($namespace, $matchNoConflict)
    {
        $subject = new PageRenderer();

        $expectedRegExp = '#<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery\\.min\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>#';
        $noConflictStatement = 'jQuery.noConflict();';
        $subject->loadJquery(null, null, $namespace);

        //jQuery.noConflict();
        $out = $subject->render();
        $this->assertRegExp($expectedRegExp, $out);

        if ($matchNoConflict) {
            $this->assertContains($noConflictStatement, $out);
        } else {
            $this->assertNotContains($noConflictStatement, $out);
        }
    }

    /**
     * @test
     */
    public function loadJqueryWithDefaultNoConflictModeDoesNotSetNamespace()
    {
        $subject = new PageRenderer();

        $expectedRegExp = '#<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery\\.min\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>#';
        $expectedStatement = 'jQuery.noConflict();';
        $subject->loadJquery(null, null, 'MyNameSpace');
        $out = $subject->render();
        $this->assertRegExp($expectedRegExp, $out);
        $this->assertContains($expectedStatement, $out);
        $this->assertNotContains('var TYPO3 = TYPO3 || {}; TYPO3.', $out);
    }

    /**
     * @test
     */
    public function loadJqueryWithNamespaceNoneDoesNotIncludeNoConflictHandling()
    {
        $subject = new PageRenderer();

        $expectedRegExp = '#<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery\\.min\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>#';
        $subject->loadJquery(null, null, PageRenderer::JQUERY_NAMESPACE_NONE);
        $out = $subject->render();
        $this->assertRegExp($expectedRegExp, $out);
        $this->assertNotContains('jQuery.noConflict', $out);
    }

    /**
     * @test
     */
    public function loadJqueryLoadsTheLatestJqueryVersionInNoConflictModeUncompressedInDebugMode()
    {
        $subject = new PageRenderer();

        $expectedRegExp = '#<script src="typo3/sysext/core/Resources/Public/JavaScript/Contrib/jquery/jquery\\.(js|\\d+\\.js|js\\?\\d+)" type="text/javascript"></script>#';
        $expectedStatement = 'jQuery.noConflict();';
        $subject->loadJquery(null, null, 'MyNameSpace');
        $subject->enableDebugMode();
        $out = $subject->render();
        $this->assertRegExp($expectedRegExp, $out);
        $this->assertContains($expectedStatement, $out);
    }

    /**
     * @return array
     */
    public function loadJqueryFromSourceDataProvider()
    {
        return [
            'google with version number' => [
                '1.6.3',
                'google',
                '#<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.js" type="text/javascript"></script>#'
            ],
            'msn with version number' => [
                '1.6.3',
                'msn',
                '#<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.3.js" type="text/javascript"></script>#'
            ],
            'jquery with version number' => [
                '1.6.3',
                'jquery',
                '#<script src="https://code.jquery.com/jquery-1.6.3.js" type="text/javascript"></script>#'
            ],
            'jquery with custom URL' => [
                '1.6.3',
                'https://my.cool.cdn/foo/jquery.js',
                '#<script src="https://my.cool.cdn/foo/jquery.js" type="text/javascript"></script>#'
            ],
        ];
    }

    /**
     * @param string $version
     * @param string $source
     * @param string $regex
     * @dataProvider loadJqueryFromSourceDataProvider
     * @test
     */
    public function isJqueryLoadedFromSourceUncompressedIfDebugModeIsEnabled($version, $source, $regex)
    {
        $subject = new PageRenderer();

        $subject->loadJquery($version, $source);
        $subject->enableDebugMode();
        $out = $subject->render();
        $this->assertRegExp($regex, $out);
    }

    /**
     * @test
     */
    public function isJqueryLoadedMinifiedFromGoogleByDefault()
    {
        $subject = new PageRenderer();

        $expectedRegex = '#<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js" type="text/javascript"></script>#';
        $subject->loadJquery('1.6.3', 'google');
        $out = $subject->render();
        $this->assertRegExp($expectedRegex, $out);
    }
}
