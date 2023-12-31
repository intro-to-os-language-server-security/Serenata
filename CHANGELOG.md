## 6.0.0 (Unreleased)
* Serenata should now run properly on PHP 8.
* Implement support for progress reporting using `$/progress`.
* Remove `serenata/serenata/didProgressIndexing` since `$/progress` is the official way of reporting progress.
* Update dependencies to latest versions (Symfony 5, PHPUnit 9, Paratest 6, ...).
* Support for _running_ Serenata on PHP 7.x has been removed as these versions are [end of life](https://www.php.net/supported-versions.php).
    * Analyzed code can still be anything from PHP 5.2 all the way up to 8.1.

## 5.4.0
* Union types from PHP 8.0 are now supported.
* [Add autocompletion when typing property types added in PHP 7.4.](https://gitlab.com/Serenata/Serenata/-/issues/331)
* [Improve overall robustness by fixing large swads of PHPStan errors.](https://gitlab.com/Serenata/Serenata/-/issues/277)
* [Fix some dependencies not being explicitly listed in `composer.json`.](https://gitlab.com/Serenata/Serenata/issues/306)
* Fix error when trying to parse broken expressions such as `$this->foo(->`.
* Add `yield from`, `object`, `mixed` and `match` to keyword autocompletions.
* Stub `textDocument/references` to avoid errors in Visual Studio Code and derivatives.
* [Improve scanning performance during indexing a bit by avoiding multiple scans of folders.](https://gitlab.com/Serenata/Serenata/-/issues/325)
* [Fix error when trying to parse expressions containing colons, such as `map(fn (): array => 5)->`.](https://gitlab.com/Serenata/Serenata/issues/332)
* [Fix property or method with the same name as a private parent property or method being incorrectly treated as override.](https://gitlab.com/Serenata/Serenata/-/issues/336)
* [Fix variable autocompletion throwing its hands in the air during closure binding (`use`) after the first character was typed.](https://gitlab.com/Serenata/Serenata/-/issues/235)
* [Fix server crashing if folders are removed during scanning for reindexing, such as when clearing the cache in Symfony projects.](https://gitlab.com/Serenata/Serenata/issues/316)
* Improve error recovery when an expression such as `$this->foo` is preceded by an unterminated expression such as `$this->bar`.
* [Fix legacy `insertText` in snippet format in completion items not having reserved characters such as `$`, `}`, and `\\` escaped in all cases.](https://gitlab.com/Serenata/Serenata/-/issues/310)
* Don't send error responses for parsing-related errors anymore, clients such as VSCode throw this in the face of the user and the code is linted as erroneous anyway.
* [Improve responsiveness when `workspace/didChangeWatchedFiles` notifications with large batches of files are sent by prioritizing user-initiated requests over indexing.](https://gitlab.com/Serenata/Serenata/-/issues/325)
* Completion items will now also send the new `tags` property from LSP 3.15.0 and fill it with the "deprecated" tag if the item is deprecated. The old `deprecated` property will also remain in place.
* Fix variable that closure is being assigned to not being suggested in closure binding (`use`), which is valid as a closure can bind the variable it's assigned to in order to create recursive closures.
* [Fix no documentation or deprecated information being processed for constants originating from `define` statements. This also caused no documentation to be displayed for built-in constants such as `CURLOPT_SSH_AUTH_TYPES`.](https://gitlab.com/Serenata/Serenata/-/issues/230)
* [Fix warning `The PHP server has errors to report: PHP Notice:  Undefined property: PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode::$types in phar:///.../DocblockTypeTransformer.php on line 43
`.](https://gitlab.com/Serenata/Serenata/-/issues/335)

## 5.3.0
* Avoid unnecessary file and folder scanning when reindexing single files.
* Try harder to remain functional instead of errorring out if duplicate imports exist.
* [Fix notice `Undefined index: endLine in phar:///.../src/Analysis/Relations/TraitUsageResolver.php on line 209`](https://gitlab.com/Serenata/Serenata/issues/306).
* Fix files starting with a dot or files inside a folder starting with a dot no longer being indexed.
    * This also caused `.phpstorm.meta.php` files to no longer be indexed.
    * This also resulted in "Could not find file Y in index" errors for these files.
* Improve error recovery when encountering unterminated expressions ending on square brackets or pararentheses such as `array_merge() $this->test` or `[] $this->test`.
* [Log warning instead of sending error response when file is not found in index or position is unknown to fix clients such as VSCode annoying user with hard error screens](https://gitlab.com/Serenata/Serenata/issues/307).
    * I'm not particularly happy with this solution. If a client requests functionality in a document the server does not know, an error response sounds appropriate because the latter cannot sanely handle the request. Returning empty responses misleads the client into thinking sort of processing happened and there were no results. Yet, VSCode insists on throwing response errors in the user's face, so this sends a warning notification instead.
* Type parsing and deduction has been rewritten to use [PHPStan's docblock parser](https://github.com/phpstan/phpdoc-parser) to allow future parsing of extended docblock types such as generic types, array shapes and callables.
* Fixed the type deducer getting confused and thinking `Foo ...$foo = null` meant `$foo` is an `array<Foo|null>` instead of an `array<Foo>|null`, since the `null` by assignment refers to the parameter here, not the other type as it does for non-variadic parameters.
* Fix possible installation breakages due to receiving incompatible versions of Doctrine libraries. These have now been made explicit.
* Fixed the docblock parsing incorrectly stripping generic syntax from types, causing them to never show up in tooltips and other places.
* [Fix overrides using non-fully qualified names and using array syntax not always resolving correctly, causing use as loop variables to fail](https://gitlab.com/Serenata/Serenata/-/issues/283).
* Using intersection types will now no longer break autocompletion:

```php
<?php

/** @var \DateTime&\Locale $a */
$a-> // Autocompletion for both DateTime and Locale are shown.
```

* Deducing types from arrays using generic syntax when fetching elements from arrays now works:

```php
<?php

/** @var array<int,B> $a */
$a = [];
$b = $a[1];

$b-> // Autocompletion for B.
```

* Deducing types from arrays using generic syntax when looping over values now works:

```php
<?php

/** @var array<int,B> $a */
foreach ($a as $b) {
    $b-> // Autocompletion for B.
}

/** @var array<B> $a */
foreach ($a as $b) {
    $b-> // Autocompletion for B.
}
```

* Deducing types from iterables using generic syntax when looping over values now works:

```php
<?php

/** @var iterable<int,B> $a */
foreach ($a as $b) {
    $b-> // Autocompletion for B.
}

/** @var iterable<B> $a */
foreach ($a as $b) {
    $b-> // Autocompletion for B.
}
```

* Deducing types and autocompletion on classes using generic syntax no longer breaks (note that resolving generics is not supported yet):

```php
<?php

/** @var Foo<int,string> $a */
$a-> // Autocompletion for Foo, as stopgap until Foo is properly resolved using int and string.
```

## 5.2.0
* Test PHP 7.4 in CI
* Fix large amounts of notices being generated whilst running on PHP 7.4
* [Stub handlers for `$/setTraceNotification` and `$/logTraceNotification`](https://gitlab.com/Serenata/Serenata/issues/308)
* Variable completion suggestions will no longer include the dollar sign in the display text
    * This is consistent with static property completions. During display, the dollar sign only added visual noise
* Return trigger characters in completion options to automatically trigger autocompletion after `->`, `::` and `$`
* _Running_ Serenata _on_ PHP 7.1 and PHP 7.2 is now deprecated
    * Both PHP 7.1 and PHP 7.2 [no longer receive active support](https://www.php.net/supported-versions.php).
    * Support will be removed no earlier than the next major release.
    * Analyzed code can still be anything from PHP 5.2 all the way up to 7.4.
    * It will probably be some time until the next major release, but it's better to mention this early.
* Fix some clients such as VSCode not properly completing variables and static properties due to dollar sign not being escaped in result
* [Return empty array for `relatedInformation` for `Diagnostic` items instead of `null` to fix errors with Sublime-LSP client](https://gitlab.com/Serenata/Serenata/merge_requests/83) (thanks to @mdeboer)
* [Fix `textDocument/didSave` notifications with `text` set to null causing errors, whilst this was valid according to the specification](https://gitlab.com/Serenata/Serenata/merge_requests/82) (thanks to @mdeboer)

## 5.1.0
* [Fix some issues with tests failing on Windows](https://gitlab.com/Serenata/Serenata/merge_requests/81) (thanks to @bpedroza)
* [Fix startup failing when using PHAR and having Xdebug enabled](https://gitlab.com/Serenata/Serenata/issues/300)
* [Fix built-in stubs not being indexed when using the PHAR distribution](https://gitlab.com/Serenata/Serenata/issues/288)
* [Add definition location support for classes in comments](https://gitlab.com/Serenata/Serenata/issues/141) (thanks to @cdaguerre)
* [Lift `rootPath` requirement in parameters sent with `initialize` request](https://gitlab.com/Serenata/Serenata/issues/299)
* [Fix ServerCapabilities incorrectly returning boolean for `codeLensProvider`](https://gitlab.com/Serenata/Serenata/issues/304)
* [Explicitly set `workspace` in ServerCapabilities to make Visual Studio Code happy](https://gitlab.com/Serenata/Serenata/issues/290)
* [Stub `workspace/didChangeConfiguration` to avoid errors when clients such as Visual Studio Code send it](https://gitlab.com/Serenata/Serenata/issues/298)
* [Fix tests failing on Windows machines with the `core.autocrlf` option set to true](https://gitlab.com/Serenata/Serenata/merge_requests/79) (thanks to @bpedroza)
* Improve performance by preventing file scanning from happening multiple times recursively instead of just once
* Return `MethodNotFound` JSON-RPC response code (`-32601`) instead of generic runtime error (`-32001`) when request method is unknown
* Try using workspace folders received during initialization if no explicit configuration is passed to support multi-folder setups without forcing explicit configuration

## 5.0.0
* Fix PHP error when initialization options were passed, but without a configuration
* See also the release notes for versions [5.0.0-RC](https://gitlab.m/Serenata/Serenata/-/tags/5.0.0-RC), [5.0.0-RC2](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC2), [5.0.0-RC3](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC3) and [5.0.0-RC4](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC4)

## 5.0.0-RC4
### Improvements
* Support PHP 7.4 arrow functions
* Support PHP 7.4 typed properties
* It is now possible to use globs, strings and regular expressions in project `excludedPathExpressions`
* [Clear entity manager on shutdown to reclaim memory when switching projects](https://gitlab.com/Serenata/Serenata/issues/237)
* [Classlike, function and constant completions now show their FQSEN in the `detail` property instead of the `label`](https://gitlab.com/Serenata/Serenata/issues/269)
* An emulative lexer is now used, allowing you to parse more recent code (with the new HEREDOC, arrow functions, ...) when running on older PHP versions as well
    * Using a PHP version that is newer or as recent as the one used by your code is still recommended, though.
* See also the release notes for versions [5.0.0-RC](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC), [5.0.0-RC2](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC2) and [5.0.0-RC3](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC3)

### Bugs Fixed
* [Set `sortText` on completion suggestions to ensure clients maintain proper ordering of result list](https://gitlab.com/Serenata/Serenata/issues/276)
* [Paths that are ignored will now no longer have their files traversed anyway](https://gitlab.com/Serenata/Serenata/issues/180)
* Fix namespace autocompletion suggestions not having their backslashes properly escaped
* Fix classlike autocompletion suggestions not having their backslashes properly escaped when starting with a leading slash
* Fix running server without a workspace configuration no longer working properly due to missing `indexDatabaseUri` key

### Changes For Clients
* [Support `textDocument/codeLens`](https://gitlab.com/Serenata/Serenata/issues/93)
    * This will display method implementations and overrides and property overrides, with commands that, when invoked, will request to open the relevant accompanying element in the client (e.g. the implemented method in the interface).
* Support `workspace/executeCommand` to execute commands returned by code actions
* [`initialize` will now generate an immediate response instead of waiting for indexing of stubs and the project to complete](https://gitlab.com/Serenata/Serenata/issues/275)
    * This fixes clients (Atom) thinking that they couldn't send any other requests yet because the server was still busy initializing. You should now get (usually limited) autocompletion and other functionality during the initial project indexing.
    * Internally, this indexing will still happen and the `didProgressIndexing` messages will still be sent, so you can still listen to these to display a progress bar.
* `didProgressIndexing` events will now also be sent when indexing happens for folders, such as after a `vendor` folder update via Composer (instead of just during initialization).
* The `didProgressIndexing` notification has been slightly altered:
    * An `originatingRequestId` is no longer sent.
    * A `folderUri` is now sent with the folder that is being indexed.
    * A `fileUri` is now sent with the file that is being indexed.
    * An `info` value is now sent with a convenient message to display to the user if desired.
* A classlike property's range will now include the access modifier and other keywords
    * This is done to remain consistent with method ranges, which also include it.
    * When multiple properties appear on one line, only the first property will include it, the second will not (or there would be overlapping ranges).

## 5.0.0-RC3
### Improvements
* [Return use statement imports for (qualified) constants when necessary](https://gitlab.com/Serenata/Serenata/issues/163)
* [Return use statement imports for (qualified) functions when necessary](https://gitlab.com/Serenata/Serenata/issues/164)
* See also the release notes for versions [5.0.0-RC](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC) and [5.0.0-RC2](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC2)

### Bugs Fixed
* [Fix `uris` in project files being ignored and not analyzed](https://gitlab.com/Serenata/Serenata/issues/243)
* [Fix crash when analyzing erroneous code containing multiple object arrows, such as `$test->->`](https://gitlab.com/Serenata/Serenata/issues/245)
* Fix classlike autocompletion suggestions being given after `use function` and `use const`
* Constant and function autocompletion suggestions will now show the FQCN, if any, to make use of libraries with namespaced constants and functions more convenient

### Changes For Clients
* [Stub `textDocument/didOpen`](https://gitlab.com/Serenata/Serenata/issues/253)
* [Stub `textDocument/didClose`](https://gitlab.com/Serenata/Serenata/issues/259)
* [Add initial support for building and distributing as PHAR](https://gitlab.com/Serenata/Serenata/issues/264)
* [Allow configuring index database location on a per-project basis](https://gitlab.com/Serenata/Serenata/issues/266)
* Return `CompletionList` as response to `textDocument/completion`
    * Sets `isIncomplete` to `true` instead of `false` (the default), preventing clients from caching completion results at some positions and trying to apply their on filtering on top of that, whilst the actual results are different, which resulted in completion seemingly not responding.
* [Implement basic support for `textDocument/documentHighlight`](https://gitlab.com/Serenata/Serenata/issues/236)
    * This is very basic for now and mainly intended to help clients to identify ranges for things such as qualified class names, i.e. the Atom client uses this to know what to underline when hovering over a qualified class name (otherwise it will do a best effort based on path separators, which stops on slashes).
* [Accept project configurations via `initializationOptions` instead of trying to read it from disk ourselves](https://gitlab.com/Serenata/Serenata/issues/258)
    * This means the server now supports initializing for projects that haven't explicitly been set up as well. A fallback configuration with an index database stored in the system's temp folder will be used in that case.
* Fix `textEdit` and `insertText` generated for completion suggestions not having backslashes properly escaped, as per spec (for edits bearing an `insertTextFormat` of `Snippet`, the backslash is itself an escape character)

## 5.0.0-RC2
* Port fixes performed in version 4.3.1
* Fix `shutdown` request not being handled correctly
* See also the release notes for version [5.0.0-RC](https://gitlab.com/Serenata/Serenata/-/tags/5.0.0-RC)

## 4.3.1
* [Fix crash when using latest dependencies related to `Interface does not exist`](https://gitlab.com/Serenata/Serenata/issues/248) (thanks to @WinterSilence)

## 5.0.0-RC
### Major Changes
* Support parsing PHP 7.3 code
* [Serenata is now officially a language server](https://gitlab.com/Serenata/Serenata/issues/91) following the [language server protocol](https://microsoft.github.io/language-server-protocol/specification)
    * [Major](https://gitlab.com/Serenata/Serenata/issues/113) [internal](https://gitlab.com/Serenata/Serenata/issues/111) [changes](https://gitlab.com/Serenata/Serenata/issues/217) were done to accommodate this.
    * Almost all requests have had changes, see [the wiki](https://gitlab.com/Serenata/Serenata/wikis/Language%20Server%20Protocol%20Support%20Table) and below for more information

### Other Improvements
* Semantic linting support has been removed as it was previously deprecated
* [All autocompletion suggestions now return data in their `textEdit` properties](https://gitlab.com/Serenata/Serenata/issues/213)
    * The `extraData.prefix` property was removed as it was a non-standard workaround that is now properly solved
* Properties, constants, function parameters now get a `mixed` type instead of no type at all if the type is not known
* [Tooltips now always displays the class name instead of varying between unqualified, partially qualified and fully qualified names based on what the original definition provided](https://gitlab.com/Serenata/Serenata/issues/220)
* [Signature help now always displays the class name instead of varying between unqualified, partially qualified and fully qualified names based on what the original definition provided](https://gitlab.com/Serenata/Serenata/issues/219)
* Due to notifications of (external or not) file changes sent by the client now being processed, changes made via command line tools or other applications - such as Composer updates - should now be picked up automatically

### Bugs Fixed
* [Fix default values of `0` being ignored (thanks to @UziTech)](https://gitlab.com/Serenata/Serenata/merge_requests/77)
* [Fix docblocks with a description of `0` being ignored (thanks to @UziTech)](https://gitlab.com/Serenata/Serenata/merge_requests/77)
* Fix off-by-one error in line returned in goto definition responses (they were 1-indexed instead of 0-indexed)
* Due to file removals now being processed, classes in removed files should no longer remain in the index after the file was removed

### Changes For Clients
* Support the following LSP messages:
    * `shutdown`
    * `initialized`
    * `workspace/didChangeWatchedFiles`
        * Also supports handling notifications of removed files now, which should fix classes remaining in index after file removal
* Rewrite the following requests to LSP messages:
    * `exit`
    * `initialize`
    * `$/cancelRequest` - was `cancel` previously
    * [`textDocument/publishDiagnostics`](https://gitlab.com/Serenata/Serenata/issues/222) - [was `lint` previously](https://gitlab.com/Serenata/Serenata/issues/222)
    * `textDocument/didChange` and `textDocument/didSave` - was `reindex` previously
    * `textDocument/completion` - was `autocomplete` previously
    * `textDocument/hover` - was `tooltip` previously
    * `textDocument/signatureHelp` - was `signatureHelp` previously
    * `textDocument/documentSymbol` - was `documentSymbols` previously
    * [`textDocument/definition` - was `gotoDefinition` previously](https://gitlab.com/Serenata/Serenata/issues/224)
    * `serenata/didProgressIndexing` - was `reindexProgressInformation` previously
    * `serenata/deprecated/getClassInfo` - was `classInfo` previously and is now deprecated
    * `serenata/deprecated/getClassListForFile` - was `classList` previously and is now deprecated
    * `serenata/deprecated/deduceTypes` - was `deduceTypes` previously and is now deprecated
    * `serenata/deprecated/getGlobalConstants` - was `globalConstants` previously and is now deprecated
    * `serenata/deprecated/getGlobalFunctions` - was `globalFunctions` previously and is now deprecated
    * `serenata/deprecated/resolveType` - was `resolveType` previously and is now deprecated
    * `serenata/deprecated/localizeType` - was `localizeType` previously and is now deprecated
* Remove the following requests:
    * `availableVariables`
    * `namespaceList`
* Other, smaller changes to request formats:
    * Drop `extraData` from autocompletion suggestions
    * [`detail` of autocompletion suggestions now also includes protection level and type information](https://gitlab.com/Serenata/Serenata/issues/218)
    * Autocompletion suggestion kinds are now numeric and follow the values prescribed by the LSP
    * All requests now accept a LSP `uri` instead of a `path`, `file` or `filename`
    * All requests now accept a proper LSP `position` instead of an `offset` and a `charoffset` flag
    * All structural element data now contains a `range` property with `start` and `end` subproperties that contain the line and character position of the element
        * The `startLine` and `endLine` properties have been removed in favor of these new properties.
    * Structural element types now no longer contain an `fqcn` property; instead, the `type` property contains the original scalar type or an FQCN if it is a class type
        * This was done to reduce redundant information as the type hint is already available separately
        * If you needed this to take over the original type definition, it is probably better to try and localize the FQCN to local imports - or even add an import for it
    * Autocompletion for class members suggestions no longer pass back `extraData.declaringStructure` or `extraData.isDeprecated`
        * It was a non-standard property and has since been replaced by generating the new `detail` property

## 4.3.0
### Improvements
* [Static members will now also be shown during non-static access](https://gitlab.com/Serenata/Serenata/issues/199)
* Autocompletion suggestions now give back a `detail` property as specified in the language server protocol
    * The `extraData.declaringStructure` properly will temporarily remain as an alias but is now deprecated and will be removed in a future version as it is now directly used to generate the `detail` property rather than leaving that to clients.
* Autocompletion suggestions now give back a `deprecated` property as specified in the language server protocol
    * The `extraData.isDeprecated` properly will temporarily remain as an alias but is now deprecated and will be removed in a future version.
* Internal (backwards-compatible) refactoring towards language server support
    * Improve autocompletion command's adherence to LSPv3
    * Use (language server) positions with character offsets in more places rather than old byte offsets
* Semantic linting support is now deprecated and will likely be removed in a future release
    * Syntax linting will remain supported
    * You can also read more about the reasoning [here](https://gitlab.com/Serenata/Serenata/wikis/Linting)
* Request processing will now use the smallest available time to process requests to maximize throughput (performance)
    * This will likely only be noticeable during large amounts of request processing, such as project indexing

### Bugs Fixed
* [Spaces are stripped in markdown code blocks inside tooltips](https://gitlab.com/Serenata/Serenata/issues/194)
* [Signature help shows for outer function call when entering closure](https://gitlab.com/Serenata/Serenata/issues/150)
* [Fix qualified constant fetches not being resolved according to use statements, causing incorrect linting as unknown function call](https://gitlab.com/Serenata/Serenata/issues/151)
* [Fix qualified function calls not being resolved according to use statements, causing incorrect linting as unknown function call](https://gitlab.com/Serenata/Serenata/issues/151)
* [Fix error relating to `calculateOffsetByLineCharacter` in rare circumstances](https://gitlab.com/Serenata/Serenata/issues/167)
* [Fix internal indexing exception in rare circumstances when docblock contains invalid HTML](https://gitlab.com/Serenata/Serenata/issues/191)
* [Fix linter complaining about mismatching docblock and parameter types when ordering differs](https://gitlab.com/Serenata/Serenata/issues/137)
* [Pass back `textEdit` in superglobal autocompletion suggestions to inform clients how to replace existing text](https://gitlab.com/Serenata/Serenata/issues/214)
* [Fix use statements added during autocompletion with same namespace prefix not always being grouped together](https://gitlab.com/Serenata/Serenata/issues/181)
* [Fix linter complaining about missing `@var` tag for properties with explicit docblock inheritance via `@inheritDoc` or variants](https://gitlab.com/Serenata/Serenata/issues/190)

## 4.2.0
### Major Changes
* [Add support for fetching document symbols via the `documentSymbols` request](https://gitlab.com/Serenata/Serenata/issues/173)

### Other Improvements
* Update `serenata/common` to at least `0.2.1`
* Update `league/html-to-markdown` to at least [4.7.0](https://github.com/thephpleague/html-to-markdown/releases/tag/4.7.0)
* Replace UUID's in database with simpler unique id via `uniqid`
    * This avoids the unnecessarily expensive generation of UUID's as well as a dependency; we just need a mostly unique identifier anyway, not anything secure
* Internal (backwards-compatible) refactoring towards language server support
    * Entities now remember whole (language server) ranges with positions instead of just start and end lines and use character offsets instead of byte offsets

### Bugs Fixed
* [Fix tilde `~` being replaced during autocompletion](https://gitlab.com/Serenata/Serenata/issues/184)
* [Fix autocompleting static properties not removing existing text (prefix)](https://gitlab.com/Serenata/Serenata/issues/212)
* [Update php-parser to fix autocompletion failing with erroneous code when updating arrays to improve convenience](https://github.com/nikic/PHP-Parser/issues/512)
* [Fix autocompleting classlike (classes, interfaces and traits) inside use statements causing more use statements to be generated](https://gitlab.com/Serenata/Serenata/issues/202)
* [Fix `self`, `parent` and `static` resolving to outer class (if present) instead of actual parent class for anonymous classes](https://gitlab.com/Serenata/Serenata/issues/192)
* Fix autocompleting qualified namespaces inside use statements not returning a prefix, which didn't allow clients to replace it properly
* Fix namespace list command returning slightly off lines for namespaces
    * It is 0-indexed, but some lines weren't - the other requests return 1-indexed data, but this inconsistency can't be fixed without a BC break

## 4.1.0
* Use Symfony Console for processing command line arguments
    * You can now use `bin/console` file for more straightforward starting
* [Fix autocompletion not working immediately after a dot or the splat operator](https://gitlab.com/Serenata/Serenata/issues/187)
* [Fix notice `Undefined property $name in src/Analysis/VariableScanningVisitor.php on line 117`](https://gitlab.com/Serenata/Serenata/issues/208)
* [Autocompletion doesn't add imports when function body is missing (but parantheses must be present)](https://gitlab.com/Serenata/Serenata/issues/204)
* [Fix error `Call to undefined method PhpParser\Node\Stmt\Trait_::isAnonymous()` in traits and interfaces](https://gitlab.com/Serenata/Serenata/issues/206)
* [Place cursor after autocompletion insertion for functions and methods if there are no required parameters](https://gitlab.com/Serenata/Serenata/merge_requests/76) (thanks to @hultberg)
* [Automatically restart without Xdebug enabled if it is present instead of just warning that performance will be degraded](https://gitlab.com/Serenata/Serenata/issues/209)
* [Fix error `Argument 3 passed to Serenata\Parsing\DocblockParser::parse() must be of the type string, null given, called in .../src/Linting/DocblockCorrectnessAnalyzer.php on line 634`](https://gitlab.com/Serenata/Serenata/issues/205)

## 4.0.1
* Fix anonymous classes not being subject to various linting inspections
* [Fix error "Call to a member function toString() on null" inside anonymous classes](https://gitlab.com/Serenata/Serenata/issues/203)
* [Autocompletion doesn't add imports when function parameter name is missing](https://gitlab.com/Serenata/Serenata/issues/204)

## 4.0.0
* PHP Integrator is now called Serenata
* Print warning when xdebug extension is loaded as to warn the user

## 3.3.0
* [Autocompletion doesn't work in incomplete foreach](https://gitlab.com/Serenata/Serenata/issues/176)
* Support specifying full URI to bind to via `--uri` parameter
  * Specifying only the port is now deprecated and will be removed in 4.0.
  * Using 0.0.0.0 as host allows sending requests to the server from across the network or inside a container, such as using Docker.

## 3.2.1
* Properly handle macOS newlines in docblocks
* Remove unnecessarily pedantic docblock sanitization
* Fix docblocks containing tabs instead of spaces not processing properly

## 3.2.0
### Major Changes
* [Support autocompletion](https://gitlab.com/Serenata/Serenata/issues/43)
  * Fuzzy matching is handled as well. This prevents large amounts of relevant suggestions being sent back to the client that are then filtered out again quickly after, which is very taxing on the socket, the client, as well as the server itself.
* [Allow cancelling requests](https://gitlab.com/Serenata/Serenata/issues/144)
  * As the core is synchronous and single-threaded, requests already being processed cannot be cancelled. However, requests are queued internally, so it is still worthwile to implement this in clients to drop pending requests.
* [Prioritize latency-sensitive requests](https://gitlab.com/Serenata/Serenata/issues/143)
  * As a result, the core can now remain responsive to requests such as autocompletion during large indexing operations, such as initial project indexing. (Note that, during initial indexing, results may not be entirely accurate as the index is still being built.)

### Other Improvements
* Implement `exit` request to request the server to shutdown safely
* Improve performance in several area's, including signature help and tooltips, due to additional internal caching that avoid recomputation
* Reformat tooltip markdown to avoid tables and to improve readability
  * Tables do not properly support paragraphs without HTML's `<br>`, which is not supported by some markdown libraries such as `marked`.
* Include parameter list in signature labels in signature help
  * These were already retrievable via the actual parameters, but some UI's, such as Visual Studio Code and atom-ide-ui, don't explicitly show the parameter label separately at the time of writing.

### Bugs Fixed
* [Fix keywords used as static members being seen as the former instead of the latter](https://gitlab.com/Serenata/Serenata/issues/149)
* Fix entities being final, resulting in Doctrine not being able to generate proxies for them
* Exclude (unusable) variables being assigned to at the requested position when providing a list of local variables
* Fix wonky docblock types such as `@throws |UnexpectedValueException` causing fatal indexing errors when used in class methods
* Fix same files erroneously being queued for reindexing when their modification date was updated, even if their contents did not change
  * They were never actually reindexed, but still reevaluated.

### Structural changes (mostly relevant to clients)
* HTML in docblocks is internally now automatically converted to markdown, so clients can always assume documentation is in markdown format
  * This is mostly relevant to old code bases and the JetBrains stubs, which use HTML rather than markdown. Newer code bases should prefer markdown as much as possible.

## 3.1.0
### Major Changes
* [Anonymous classes are now properly supported](https://gitlab.com/Serenata/Serenata/issues/8)
* [Indexing performance has been improved in various ways, for both small and large files](https://gitlab.com/Serenata/Serenata/issues/139)
* [A new command `GotoDefinition` to provide code navigation has been added](https://gitlab.com/Serenata/Serenata/issues/42)
  * Class names inside comments are currently no longer supported, [but this may change in the future](https://gitlab.com/Serenata/Serenata/issues/141). This should however pose less of a problem now, as docblock types should be accompanied by type hints, which are clickable.
  * This moves us one step closer to becoming a language server in the long run.
* [Folder indexing requests are now transparently split up into multiple file index requests](https://gitlab.com/Serenata/Serenata/issues/123)
  * This will allow for request cancelation and prioritization in the future.

### Bugs Fixed
* [Fix using traits in interfaces crashing the server](https://gitlab.com/Serenata/Serenata/issues/133)
* [Fix tooltips not working on grouped use statements](https://gitlab.com/Serenata/Serenata/issues/136)
* [Fix project paths containing the tilde not being expanded to the user's home folder](https://gitlab.com/Serenata/Serenata/merge_requests/72)
* Fix core shrugging and bailing whenever the entity manager closed due to a database error
* [Fix unsupported meta file static method types throwing an error instead of being silently skipped](https://gitlab.com/Serenata/Serenata/issues/130)
* Fix some edge case bugs with name (type) resolution by upgrading to [name-qualification-utilities 0.2.0](https://gitlab.com/Serenata/name-qualification-utilities/blob/master/CHANGELOG.md#020)
* [Fix function and method docblock `@return` tag types not being validated against the actual return type](https://gitlab.com/Serenata/Serenata/issues/94)
* [Fix crash with variable expressions in method calls during type deduction of the expression based on meta files](https://gitlab.com/Serenata/Serenata/issues/134)
* [Make disk I/O and locked database errors propagate as fatal errors, as they currently can't be recovered from and to notify the user](https://github.com/Gert-dev/php-ide-serenata/issues/278)
* [Fix folder scanning occurring twice during indexing, once for counting the total amount of items (for progress streaming) and once for actual indexing](https://github.com/Gert-dev/php-ide-serenata/issues/314#issuecomment-320315228)
* [Fix occasional "Position out of bounds" logic exception during requests, such as signature help, containing code not explicitly indexed beforehand](https://gitlab.com/Serenata/Serenata/issues/126)
* Fix bodies of anonymous classes not being subject to any parsing or linting
  * This fixes use statements not being identified as used, among other issues
* [Fix initialize command failing to reinitialize when database was locked or I/O errors occurred](https://github.com/Gert-dev/php-ide-serenata/issues/278)
  * This happened in spite of the original database connection being closed and the database itself completely being removed due to the WAL and SHM files lingering. This seems to cause sqlite to try and reuse them for the new database during schema creation afterwards, which in turn resulted in never being able to break the chain of errors without removing all database files manually.

### Structural changes (mostly relevant to clients)
* Properties now also return a `filename` property, which was missing before
* The namespace list will now return a map of ID's to values rather than just values, consistent with other lists
* Anonymous classes are now included in class lists, carrying a special name and FQCN so they can be easily distinguished
  * Classes now also include a new `isAnonymous` field that is set to `true` for these classes.
* The `reindex` command no longer takes a `stream-progress` argument (it will be silently ignored)
  * Progress is now only streamed for folder index requests and is always on. If you don't want these notifications, you can simply ignore them.

## 3.0.0
### Major changes
* [PHP 7.1 is now required to _run_ the core](https://gitlab.com/Serenata/Serenata/issues/81)
  * Code that is analyzed can still be anything from PHP 5.2 all the way up to 7.1.
* [PHP 7.1 is now properly supported](https://gitlab.com/Serenata/Serenata/issues/40)
  * It already parsed before, but this involves properly detecting the new scalar types, multiple exception types, ...
* [Various lists containing large data, such as the constant, function, structure and namespace list are no longer rebuilt every time a command to fetch them was invoked](https://gitlab.com/Serenata/Serenata/issues/122)
  * This is primarily used by the autocompletion Atom package, which will benefit from an improvement in response times and fewer minor hiccups.
* [HTML will no longer be stripped from docblock descriptions and text (except in places where it's not allowed, such as in types)](https://gitlab.com/Serenata/Serenata/issues/7)
  * This means you can use HTML as well as markdown in docblocks and the client side is now able to properly format it.
*  [PhpStorm's open source stubs are now used for indexing built-in structural elements](https://gitlab.com/Serenata/Serenata/issues/2)
  * Reflection in combination with PHP documentation data is no longer used to index built-in items.
  * These provide more accurate parameter type, return type and default value information than the documentation for the purpose of static analysis (e.g. `DateTime::createFromFormat`).
  * This reduces the maintenance burden of having two separate indexing procedures and lowers the test surface.
  * `isBuiltin` was removed for classlikes, global functions and global constants. This could previously be used for features such as code navigation since there was no physical file for the built-in items. Clients can now remove conditional code checking for this property as bulit-in items are indexed like any other code.
* [(PhpStorm) Meta files are now supported in a very rudimentary way, albeit with some restrictions (which may be lifted in the future)](https://gitlab.com/Serenata/Serenata/issues/10)
  * Only the `STATIC_METHOD_TYPES` setting is supported.
  * Only [the first version of the format](https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata#PhpStormAdvancedMetadata-Deprecated:Legacymetadataformat(2016.1andearlier)) is supported, as this is likely the most widely used variant.
  * The settings must be located in a namespace called `PHPSTORM_META`. It is recommended to place it in a file called `.phpstorm.meta.php` for compatibility with PhpStorm, but in theory any PHP file can contain this namespace.
  * The "templated" argument must always be the first one.
  * The class name must directly refer to the class, i.e. meta information for parent classes or interfaces will not automatically cascade down to children and implementors.

```php
// ----- .phpstorm.meta.php
<?php

namespace PHPSTORM_META {
    use App;

    $STATIC_METHOD_TYPES = [
        App\ServiceLocator::get('') => [
            'someService' instanceof App\SomeService
        ]
    ];
}
```
```php
// ----- src/App/ServiceLocator.php
<?php

namespace App;

class ServiceLocator
{
    public function get(string $name)
    {
        // ...
    }
}
```
```php
// ----- src/app/Main.php
<?php

$serviceLocator = new ServiceLocator();
$serviceLocator->get('someService')-> // Autocompletion for App\SomeService
```

### Linting
* [Some docblock warnings have been promoted to errors](https://gitlab.com/Serenata/Serenata/issues/33)
* [Complain about missing ampersand signs for reference parameters in docblocks](https://gitlab.com/Serenata/Serenata/issues/32)
* [Don't complain about type mismatches in docblocks when the qualifications of the types are different](https://gitlab.com/Serenata/Serenata/issues/89)
* [For docblock parameters, specializations of the type hint are now allowed to narrow down class types](https://gitlab.com/Serenata/Serenata/issues/35)

```php
<?php

// For interfaces
interface I {}
class A implements I {}
class B implements I {}

/**
 * @param A|B $i <-- Ok, A and B both implement I and pass the type hint.
 */
function foo(I $i) {}

// For classes
class C {}
class A extends C {}
class B extends C {}

/**
 * @param A|B $c <-- Ok, A and B both extend C and pass the type hint.
 */
function foo(C $c) {}
```

* [Processing more complex docblock types, such as compound types containing multiple array specializations and null, has substantially improved and should complain less about valid combinations](https://gitlab.com/Serenata/Serenata/issues/11)
* Linting messages for classlikes, functions and methods will now be properly shown over their name instead of on the first character of their definition
* Disabling unknown global constant linting now works again
* For docblock parameters, compound types containing class types will now be resolved properly (previously, only a single type was resolved)
* It is now possible to disable linting missing documentation separately from linting docblock correctness
* The fully qualified name of a global function that wasn't found (instead of just the local name)
* The fully qualified name of a global constant that wasn't found (instead of just the local name)
* Instead of an associative array, a flat list of error and warning messages will now be returned
  * The list will include the message and the range (offsets) it applies in. Other data, including the line number, is no longer included.
* Messages have become more concise and verbal baggage has been removed from them
  * Mentioning the name was redundant as the location of the linter message provides the necessary context.
  * Instead of `Docblock for constant FOO is missing @var tag`, the message will now read `Constant docblock is missing @var tag`.
  * This also increases readability, as markdown is no longer used (since it is not allowed by the language server protocol nor supported by Atom's linter v2 anymore).

### Various enhancements
* Updated dependencies
* Traits using other traits are now supported
* Default values for parameters will be used to deduce their type (if it could not be deduced from the docblock or a type hint is omitted)
* Fatal server errors will now include a much more comprehensive backtrace, listing previous exceptions in the exception chain as well
* Specialized array types containing compound types, such as `(int|bool)[]`, are now supported. This primarily affects docblock parameter type linting, as it's currently not used anywhere else
* Parsing default values of structural elements now doesn't happen twice during indexing anymore, improving indexing performance

### Various bugfixes
* [Fix incorrect type deduction for global functions without leading slash](https://github.com/Gert-dev/php-ide-serenata/issues/284)
* [Deducing the type of anonymous classes no longer generates errors](https://gitlab.com/Serenata/Serenata/issues/106)
* [Requests for files that are not in the index will now be properly denied where applicable instead of resulting in a logic exception being thrown](https://gitlab.com/Serenata/Serenata/issues/104)
* [When a circular dependency or reference occurs, requests for the culprit class should now continue working, albeit without the duplicate information](https://gitlab.com/Serenata/Serenata/issues/79)
* Fixed the type of defines not being properly deduced from their value
* Fix not being able to use the same namespace multiple times in a file
* Fix no namespace (i.e. before the first namespace declaration) being confused for an anonymous namespace when present
* Fixed trait aliases without an explicit access modifier causing the original access modifier getting lost
* The docblock parser will no longer trip over leading and trailing bars around compound types (e.g. `@param string| $test` will become `@param string $test`)
* The variable defined in a `catch` block wasn't always being returned in the variable list

### Structural changes (mostly relevant to clients)
* [A new command `Tooltips` to provide tooltips has been added](https://gitlab.com/Serenata/Serenata/issues/86)
* [The invocation info command has been reworked into the `SignatureHelp` command (call tips)](https://gitlab.com/Serenata/Serenata/issues/92)
  * This command operates in a similar fashion, but provides full information over the available signatures instead of just information about the invocation, leaving the caller to handle further type determination and handling.
* `SemanticLint` has been renamed to just `Lint`, as it also lints syntax errors
* The class list will now only provide fields directly relevant to the class.
  * Most of the related data, such as methods and constants, were already being filtered out for performance reasons.
  * In order to fetch more information about a class, such as its parents, you now have to manually fetch this using the class info command.
* `isNullable` will no longer be returned for function and method parameters
  * This was inconsistent with return type information for functions and methods (it also didn't have an `isNullable`).
  * It didn't properly take docblock information into account, so it was actually more of an "is type hint nullable".
  * Whether or not a type is nullable, taking all factors into account (the type hint, a default value of `null`, the docblock types), can already be deduced from the actual type list (`null` will be present in it).
  * Whether the type hint should be nullable, which can be important when overriding methods, where the signatures must match, is now no longer something the client needs to worry about as the `typeHint` property will now include a PHP 7.1 question mark if the original type hint also included one.
* Data related to `throws` is now returned as an array of arrays, each with a `type` and a `description` key instead of an associative array mapping the former to the latter
  * This is recommended by [phpDocumentor](https://phpdoc.org/docs/latest/references/phpdoc/tags/throws.html).
  * This allows the same exception type to be referenced multiple times to describe it being thrown in different situations.
* The `LocalizeType` command will no longer make any chances to names that aren't fully qualified, as they are already "local" as they are
* The `verbose` option for the `reindex` command was removed. It was a hidden feature and hasn't been used in quite some time (it was originally used for testing, but actual tests have replaced it)
* Namespaces supplied by the `NamespaceList` command will now always have a start and end line (no more `null` for the last namespace)
* The `class` keyword returned as constant will now have a file, start line and end line (which are the same as the class it belongs to). It will also have a default value which is equal to the class name without leading slash
* Anonymous namespaces supplied by the `NamespaceList` command will now always have `null` as name instead of an empty string for explicitly anonymous namespaces and `null` for implicitly anonymous namespaces, as they are both the same
* The `shortName` property for classlikes is now called `name`, the FQCN can now be found in `fqcn`. This is more logical than having `name` contain the FQCN and `shortName` contain the short name
* `declaringClass.name` was renamed to `declaringClass.fqcn` for consistency
* The return type hint for functions and methods and type hints for parameters will now always be an FQCN in the case of non-scalar types
  * The non-resolved type provided no context and could be ambiguous.
  * If the type needs to be relative to local imports, you can always localize the type using the appropriate command.
    * In the case of the atom-refactoring package, this will fix the issue where stubbing an interface method would get the return type hint wrong in the stub, because it was attempting to localize a type that wasn't fully qualified in the first place (at least if the original interface method also didn't use an FQCN).
* Fixed the short and long description for classlikes being an empty string instead of `null` when not present
* Fixed the short, long and type description for global and class constants being an empty string instead of `null` when not present
* Fixed the short, long and type description for properties being an empty string instead of `null` when not present
* Fixed the short, long and return description for functions and methods being an empty string instead of `null` when not present
* Namespaces provided by the namespace list command will now also include the path to the file that they are present in
* `declaringStructure.name` was renamed to `declaringStructure.fqcn` for consistency
* `isAbstract`, `isFinal`, `isAnnotation`, `interfaces` and `directInterfaces` will no longer be returned for interfaces and traits as they are only relevant for classes
* `directImplementors` will no longer be returned for classes and traits as it is only relevant for interfaces
* `directTraitUsers` will no longer be returned for classes and interfaces as it is only relevant for traits
* `parents`, `directParents` and `directChildren` will no longer be returned for traits as they are only relevant for classes and interfaces
* `traits` and `directTraits` will no longer be returned for interfaces as they are only relevant for classes and traits
* `isPublic`, `isProtected` and `isPrivate` will no longer be returned for global constants as they are only relevant for class constants
* `fqcn` will no longer be returned for class constants as it is only relevant for global constants
* `fqcn` will no longer be returned for methods (class functions) as it is only relevant for global functions

## 2.1.7
* Lock php-parser at 3.0.5 to avoid recent PHP 7 requirement in its master due to Composer limitation.

## 2.1.6
* Fix error with incomplete default values for define expressions causing the error `ConfigurableDelegatingNodeTypeDeducer::deduce() must implement interface PhpParser\Node, null given` (https://gitlab.com/Serenata/Serenata/issues/87).
* Fix this snippet of code causing php-parser to generate a fatal error:

```php
<?php

function foo()
{
    return $this->arrangements->filter(function (LodgingArrangement $arrangement) {
        return
    })->first();
}
```

## 2.1.5
* Indexing performance was slightly improved.
* Fix regression where complex strings with more complex interpolated values wrapped in parantheses were failing to parse, causing indexing to fail for files containing them (https://gitlab.com/Serenata/Serenata/issues/83).

## 2.1.4
* Fix corner case with strings containing more complex interpolated values, such as with method calls and property fetches, failing to parse, causing indexing to fail for files containing them (https://gitlab.com/Serenata/Serenata/issues/83).

## 2.1.3
* Fix corner case with HEREDOCs containing interpolated values failing to parse, causing indexing to fail for files containg them (https://gitlab.com/Serenata/Serenata/issues/82).
* Default value parsing failures will now throw `LogicException`s.
  * This will cause them to crash the server, but that way they can be debugged as parsing valid PHP code should never fail.

## 2.1.2
* Fix `@throws` tags without a description being ignored.
* Fix symlinks not being followed in projects that have them.
* Terminate if `mbstring.func_overload` is enabled, as it is not compatible.

## 2.1.1
* Fix the `static[]` not working properly when indirectly resolved from another class (https://github.com/php-integrator/atom-autocompletion/issues/85).

## 2.1.0
* A couple dependencies have been updated.
* Composer dependencies are now no longer in Git.
* Fix `self`, `static` and `$this` in combination with array syntax not being resolved properly (https://github.com/php-integrator/atom-autocompletion/issues/85).

## 2.0.2
* Fix a database transaction not being terminated correctly when indexing failed.
* Fix constant and property default values ending in a zero (e.g. `1 << 0`) not being correctly indexed.
* Fix an error message `Call to a member function handleError() on null` showing up when duplicate use statements were found.

## 2.0.1
* Fix the class keyword being used as constant as default value for properties generating an error.
* Fix (hopefully) PHP 7.1 nullable types generating parsing errors.
  * This only fixes them generating errors during indexing, but they aren't fully supported just yet.

## 2.0.0
### Major changes
* PHP 5.6 is now required. PHP 5.5 has been end of life for a couple of months now.
  * If you're running the server and upgrading is truly not an option at the moment, you can temporarily switch back the version check in the Main.php file as currently no PHP 5.6 features are used yet. However, in due time, they might.
* A great deal of refactoring has occurred, which paved the way for performance improvements in several areas, such as type deduction.
  * Indexing should be slightly faster.
  * Everything should feel a bit more responsive.
  * Semantic linting should be significantly faster, especially for large files.
* Passing command line arguments is no longer supported and has been replaced with a socket server implementation. This offers various benefits:
  * Bootstrapping is performed only once, allowing for responses from the server with lower latency.
  * Only a single process is managing a single database. This should solve the problems that some users had with the database suddenly being locked or unwritable.
  * Only a single process is spawned. No more spawning concurrent processes to perform different tasks, which might heavily burden the CPU on a user's system as well as has a lot of overhead.
    * Sockets will also naturally queue requests, so they are handled one by one as soon as the server is ready.
  * Caching is no longer performed via file caching, but entirely in memory. This means users that don't want to, don't know how to, or can't set up a tmpfs or ramdisk will now also benefit from the better performance of memory caching.
    * Additionally this completely obsoletes the need for wonky file locks and concurrent cache file access.

### Commands
* A new command, `namespaceList`, is now available, which can optionally be filtered by file, to retrieve a list of namespaces. (thanks to [pszczekutowicz](https://github.com/pszczekutowicz))
* `resolveType` and `localizeType` now require a `kind` parameter to determine the kind of the type (or rather: name) that needs to be resolved.
  * This is necessary to distinguish between classlike, constant and function name resolving based on use statements. (Yes, duplicate use statements may exist in PHP, as long as their `kind` is different).
* `implementation` changed to `implementations` because the data returned must be an array instead of a single value. The reasoning behind this is that a method can in fact implement multiple interface methods simultaneously (as opposed to just one).
* The `truncate` command was merged into the `initialize` command. To reinitialize a project, simply send the initialize command a second time.
* `invocationInfo` will now also return the name of the invoked function, method or constructor's class.
* `invocationInfo` now returns `method` instead of `function` for class methods (as opposed to global functions).
* `deduceTypes` now expects the full expression to be passed via the new `expression` parameter. The `part` parameter has been removed.

### Global functions and constants
* Unqualified global constants and functions will now correctly be resolved.
* Semantic linting was incorrectly processing unqualified global function and constant names.
* Use statements for constants (i.e. `use const`) and functions (i.e. `use function`) will now be properly analyzed when checking for unused use statements.

### Docblocks and documentation
* In documentation for built-in functions, underscores in the description were incorrectly escaped with a slash.
* In single line docblocks, the terminator `*/` was not being ignored (and taken up in the last tag in the docblock).
* Class annotations were sometimes being picked up as being part of the description of other tags (such as `@var`, `@param`, ...).
* `@noinspection` is no longer linted as invalid tag, so you can now not be blinded by errors when reading the code of a colleague using PhpStorm.
* Variadic parameters with type hints were incorrectly matched with their docblock types and, by consequence, incorrectly reported as having a mismatching type.

### Type deduction
* The indexer was assigning an incorrect type to variadic parameters. You can now use elements of type hinted variadic parameters as expected in a foreach:

```php
protected function foo(Bar ...$bars)
{
    foreach ($bars as $bar) {
        // $bar is now an instance of Bar.
    }
}
```

* The type deducer can now (finally) cope with conditionals on properties, next to variables:

```php
class Foo
{
    /**
     * @var \Iterator
     */
    protected $bar;

    public function fooMethod()
    {
        // Before:
        $this->bar = new \SplObjectStorage();
        $this->bar-> // Still lists members of Iterator.

        if ($this->bar instanceof \DateTime) {
            $this->bar-> // Still lists members of Iterator.
        }

        // After:
        $this->bar = new \SplObjectStorage();
        $this->bar-> // Lists members of SplObjectStorage.

        if ($this->bar instanceof \DateTime) {
            $this->bar-> // Lists members of DateTime.
        }
    }
}
```

* Type deduction with conditionals has improved in many ways, for example:

```php
if ($a instanceof A || $a instanceof B) {
    if ($a instanceof A) {
        // $a is now correctly A instead of A|B.
    }
}
```

```php
$b = '';

if ($b) {
    // $b is now correctly string instead of string|bool.
}
```

* Array indexing will now properly deduce the type of array elements if the type of the array is known:

```php
/** @var \DateTime[] $foo */
$foo[0]-> // The type is \DateTime.
```

### Other
* The default value of defines was not always correctly being parsed.
* Heredocs were not correctly being parsed when analyzing default values of constants and properties.
* Attempting to index a file that did not meet the passed allowed extensions still caused it to be added to the index.
* Assigning a global constant to something caused the type of that left operand to become the name of the constant instead.
* The `class` member that each class has since PHP 5.5 (that evaluates to its FQCN) is now returned along with class constant data.
* Use statements were incorrectly reported as unused when they were being used as extension or implementation for anonymous classes.
* PHP setups with the `cli.pager` option set will now no longer duplicate JSON output. (thanks to [molovo](https://github.com/molovo))
* Parantheses inside strings were sometimes interfering with invocation info information, causing the wrong information to be returned.
* When encountering UTF-8 encoding errors, a recovery will be attempted by performing a conversion (thanks to [Geelik](https://github.com/Geelik)).
* The type of built-in global constants is now deduced from their default value as Reflection can't be used to fetch their type nor do we have any documentation data about them.
* Previously a fix was applied to make FQCN's actually contain a leading slash to clearly indicate that they were fully qualified. This still didn't happen everywhere, which has been corrected now.
* When a class has a method that overrides a base class method and implements an interface method from one of its own interfaces, both the `implementation` and `override` data will now be set as they are both relevant.
* Parent members of built-in classlikes were being indexed twice: once for the parent and once for the child, which was resulting in incorrect inheritance resolution results, unnecessary data storage and a (minor) performance hit.
* Built-in interfaces no longer have `isAbstract` set to true. They _are_ abstract in a certain sense, but this property is meant to indicate if a classlike has been defined using the abstract keyword. It was also not consistent with the behavior for non-built-in interfaces.
  * Built-in interface methods also had `isAbstract` set to `true` instead of `false`.

## 1.2.0
* Initial split from the [Gert-dev/php-ide-serenata](https://github.com/Gert-dev/php-ide-serenata) repository. See [its changelog](https://github.com/Gert-dev/php-ide-serenata/blob/master/CHANGELOG.md) for what changed in older versions.
