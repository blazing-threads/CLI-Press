# CLI Press

*CLI Press* builds beautiful documentation from the command line.  Whether you want to create a guide for your latest application, throw together some notes, or fashion a textbook for the masses, *CLI Press* will do the job.  Write your content with *Pressdown*, an extension of *Markdown Extra*, and let *CLI Press* do the rest.  Or create your own layouts, and express your own style!  *CLI Press* is built to be as simple or as complex as you need it to be with an emphasis on minimal configuration.
 
# Requirements

*CLI Press* uses [wkhtmltopdf](http://wkhtmltopdf.org) to generate a PDF from standard HTML/JavaScript/CSS files.  It also uses *Pressdown*, an extension of *Markdown Extra*, so you can write your content using a mixture of Markdown Extra, CSS, HTML, and features created just for *CLI Press* that are optimized for PDF generation rather than website generation.  

# Installation

```
composer require --global blazing-threads/cli-press
```

This will install the `cli-press` script as a global requirement and add it to composer's *bin* folder.  Be sure this *bin* folder (usually found in `~/.composer/vendor/bin`) is in your `PATH` environment variable for easiest use.

# Usage

The simplest usage is just to invoke *CLI Press* in a directory with document files using the `generate` command.

```
cli-press generate
```

*CLI Press* will recursively search for all files with parseable extensions, including **.md**, **.html**, and **.pd** and generate a PDF.  All files are globbed together in file-system sort order.  You can name them in such a way that they will be processed in the order you intend, or you can use an interactive *CLI Press* command to layout your final document.  This command generates a configuration file that gets placed in the root folder.
 
Sub-directories become chapters or sections of the final document, with subsequently nested sub-directories becoming sub-chapters or sub-sections.
  
# Layout & Styling

*CLI Press* has built-in layouts for creating a documentation cover, a table of contents, chapter/sub-chapter title pages, and content page headers and footers.  It also comes with a style for creating font and color schemes to adorn document features like headers, links, code blocks, horizontal rules, and traditional textbook elements like insets, tables, diagrams, and captions.  Everything can be customized, either partially or totally, using a number of different methods, each built to suit specific needs.  The layout system uses the Twig templating engine and has the ability to create sophisticated template inheritance and extension.  Because the final document is rendered from HTML/CSS, custom styles can be created with the familiar, flexible, and powerful rules of cascading style sheets.  *CLI Press* can also use a configuration file to override simple style rules like fonts and colors.
 
 # Pressdown
 
 Think of *Pressdown* as a combination of Markdown syntax and Bootstrap styling combined into a single interface.  It allows you to put the powerful, but verbose, syntax of a consistent and optimized style like Bootstrap into the simple, yet limited, syntax of a markup-light system like Markdown.  And it's been designed specifically for creating documentation, not HTML meant to be displayed in a browser.  In short, *Pressdown* is the right tool for the job.

