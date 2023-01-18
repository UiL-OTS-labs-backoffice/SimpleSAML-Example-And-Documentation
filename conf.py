# Configuration file for the Sphinx documentation builder.
#
# For the full list of built-in configuration values, see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Project information -----------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#project-information
project = 'CDH Federated Authentication'
copyright = '2023, Centre for Digital Humanities, Utrecht University'
author = 'Centre for Digital Humanities, Utrecht University'

# -- General configuration ---------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#general-configuration

extensions = [
    'sphinx.ext.autosectionlabel',
    'uu_sphinx_theme',
]

templates_path = ['_templates']
exclude_patterns = ['.github', '.env', 'venv', '_build', 'Thumbs.db',
                    '.DS_Store']

# -- Options for HTML output -------------------------------------------------
# https://www.sphinx-doc.org/en/master/usage/configuration.html#options-for-html-output

# html_theme = 'alabaster'

html_theme = "uu_sphinx_theme"
html_theme_path = []

html_static_path = ['_static']


from sphinx.highlighting import lexers
from pygments.lexers.php import PhpLexer

# enable highlighting for PHP code not between <?php ... ?> by default
lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
