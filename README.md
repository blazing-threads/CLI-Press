# CLI Press

*CLI Press* builds beautiful documentation from the command line.  Whether you want to create a guide for your latest application, throw together some notes, or fashion a textbook for the masses, *CLI Press* will do the job.  Write your content with *Pressdown*, an extension of *Markdown Extra*, and let *CLI Press* do the rest.  Or create your own layouts, and express your own style!  *CLI Press* is built to be as simple or as complex as you need it to be with an emphasis on minimal configuration.
 
# Requirements

*CLI Press* uses [wkhtmltopdf](http://wkhtmltopdf.org) to generate a PDF from standard HTML/CSS/JavaScript files.  It also uses *Pressdown*, an extension of *Markdown Extra*, so you can write your content using a mixture of Markdown Extra, CSS, HTML, and features created just for *CLI Press* that are optimized for PDF generation rather than website generation.  

# Installation

```
composer require --global blazing-threads/cli-press
```

This will install the `cli-press` script as a global requirement and add it to composer's *bin* folder.  Be sure this *bin* folder (usually found in `~/.composer/vendor/bin`) is in your `PATH` environment variable for easiest use.

After installation, you can configure *CLI Press* with the below command.  This configuration lets the press know where your personal and system themes are stored.

```
cli-press configure
```

# Usage

The simplest usage is just to invoke *CLI Press* in a directory with document files using the `generate` command.  This will apply the default theme with the built-in *CLI Press* templates and styling.

```
cli-press generate
```

*CLI Press* will recursively search for all files with parseable extensions, including **.md**, **.html**, and **.pd** and generate a PDF.  All files are globbed together in file-system sort order.  You can name them in such a way that they will be processed in the order you intend.
 
Sub-directories become chapters or sections of the final document, with subsequently nested sub-directories becoming sub-chapters or sub-sections.  Each directory (and thus each chapter) can have it's own styling.
  
# Layout & Styling

Layout and styling are built using a combination of press instructions and template files.  *CLI Press* has templates for creating a documentation cover, a table of contents, chapter/sub-chapter title pages, and content page headers and footers.  It uses press instructions for creating font and color schemes to adorn document features like headers, links, code blocks, horizontal rules, and traditional textbook elements like insets, tables, diagrams, and captions.  Everything can be customized, either partially or totally, using a number of different methods, each built to suit specific needs.  The layout system uses the Twig templating engine and has the ability to create sophisticated template inheritance and extension.  Because the final document is rendered from HTML/CSS, custom styles can be created with the familiar, flexible, and powerful rules of cascading style sheets.  *CLI Press* can also use press instruction files to override simple style rules like fonts and colors as well as to set the theme.

# Themes

A theme defines press instructions to handle styling and templates to handle layouts.  There is a default theme shipped with *CLI Press* but you can also create custom themes.  You can configure it to look in two theme directories, a personal one and a system one.  Then you can share community themes as well as have personal themes.  Templates and press instructions are resolved in order so personal themes can override system themes which will override the default theme.  Additionally, press instructions can be placed in any directory to override all other settings.  Finally, you can also define instance themes that are included with the documentation files.  These instance themes can be full-blown themes in their own right, or be used to override personal, system, or base themes.

# Presets

With presets you can define a set of press instructions to give to *CLI Press*.  These operate much like a theme but change only press instructions and not templates.  Unlike themes, however, multiple presets can be used at one time.  For instance, you might have a base preset that handles most of your styling, but you want a slightly different styling for a specific section.  You would use the base preset in all sections, but in that specific section you would also apply a second preset that contains the handful of overrides needed for it. 
 
# Pressdown
 
Think of *Pressdown* as a combination of Markdown syntax and Bootstrap styling combined into a single interface.  It allows you to put the powerful, but verbose, syntax of a consistent and optimized style like Bootstrap into the simple, yet limited, syntax of a markup-light system like Markdown.  And it's been designed specifically for creating documentation, not HTML meant to be displayed in a browser.  In short, *Pressdown* is the right tool for the job.

# Cover Pages

If there is a *cover.md* file in a directory, `cli-press` will use it's contents to generate a cover page for the chapter or the whole document if it is in the root directory.  *CLI Press* also comes with a simple cover page default that can be used, one for the document cover and one for the chapter cover.

# Assets

You can include assets like images, fonts, etc. in your theme's CSS, html, and layout files by using `file://` links to them.  Use the press instruction `custom-assets` to set the path to a directory in your project where the assets are located.  Then, in your theme files, use a Twig expression to resolve the asset like this:

```
img.castle {
    content: url({{ customAsset('/images/castle.png') }});
}
```

The asset files from personal and system themes can be overridden by using the same filenames (and path structure).  *CLI Press* will look for a particular asset in all defined custom asset paths and use the first one that it finds. 

# The Nitty Gritty

Coming soon: details on themes, press instructions, presets, and more.  For now you can check out this [Intro to Pressdown](./intro-to-pressdown.pdf) created with, what else?, *CLI Press*.

# Bonus

Do you like Font Awesome?  If so, good news!  You can use some shorthand to put your favorite icons right in your documentation.  Here are some examples:

```
This: {f@chevron-left}

Becomes: <i class="fa fa-chevron-left"></i>

This: {f@search 4x}

Becomes: <i class="fa fa-search fa-4x"></i>

This: {f@shield rotate-270 lg}

Becomes: <i class="fa fa-shield fa-rotate-270 fa-lg"></i>
```

I think you get the picture.  Size, rotation, and flipping are the only supported class transformations.  Currently, if `cli-press` detects any Font Awesome patterns, it will automatically include a CDN of the Font Awesome CSS file for version 4.7, otherwise you will need to do that yourself.