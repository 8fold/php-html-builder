<ol>
    <li>Start PHP server and point to `/comparisons`</li>
    <li>Launch browser to the root address</li>
    <li>Click link of page to be tested</li>
    <li>Note displayed render time in milliseconds</li>
    <li>Refresh browser 11 times; making not of render times (should result in 12 times)</li>
    <li>Calculate average and median</li>
</ol>
<dl>
    <dt><a href="/html/">Plain HTML</a></dt>
    <dd>Has minimal PHP for measuring time</dd>
    <dt><a href="/php/">PHP</a></dt>
    <dd>Replicates the Plain HTML using common patterns using PHP as a template engine.</dd>
    <dt><a href="/php-html-builder-element/">PHP HTML Builder (element)</a></dt>
    <dd>Replicates the PHP variant using PHP HTML Builder Element to generate the `article` and `section` elements. Does not use Composer autoloading.</dd>
    <dt><a href="/php-html-builder-document/">PHP HTML Builder (document)</a></dt>
    <dd>Replicates the PHP variant using PHP HTML Builder Document to generate the whole HTML output. Does not use Composer autoloading.</dd>
    <dt><a href="/php-html-builder-document-psr4/">PHP HTML Builder (document) w/ PSR-4</a></dt>
    <dd>Replicates the PHP HTML Builder (document) variant using PSR-4 autoloading via Composer.</dd>
</dl>

