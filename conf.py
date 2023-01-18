# Configuration file for the Sphinx documentation builder.
#
# For the full list of built-in configuration values, see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Project information -----------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#project-information

### TMP TODO remove
import sys
import os
sys.path.insert(0, os.path.abspath('/home/ty/projects/python/'))
### END TMP

project = 'CDH Federated Authentication'
copyright = '2023, Centre for Digital Humanities, Utrecht University'
author = 'Centre for Digital Humanities, Utrecht University'

# -- General configuration ---------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#general-configuration

extensions = [
    'sphinx.ext.autosectionlabel'
]

templates_path = ['_templates']
exclude_patterns = ['.github', '.env', 'venv', '_build', 'Thumbs.db',
                    '.DS_Store']

# -- Options for HTML output -------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#options-for-html-output

# html_theme = 'alabaster'

# TODO: repo
html_theme = "sphinx-theme"
html_theme_path = [
    '/home/ty/projects/python/'
]

html_static_path = ['_static']


from sphinx.highlighting import lexers
from pygments.lexers.php import PhpLexer

# enable highlighting for PHP code not between <?php ... ?> by default
lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
