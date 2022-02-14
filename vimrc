" basic setting
set nocp                " vi not compatible mode
set nu                  " show line number
syntax on               " code highlight
colorscheme desert      " highlight theme dracula
set tabstop=4           " think 4 space to 1 tab, think '\t' to 4 space
set softtabstop=4       " tab input to 4 space width
set shiftwidth=4        " change line width is 4 space
set expandtab           " input tab, true 4 space
set autoindent          " auto indent
set smartindent         " smart indent
set ruler               " show cursor pos status
set cursorline          " highlight cursor line
set cursorcolumn        " highlight cursor line
filetype indent on      " different filetype use differnt indent
" set laststatus=2        " always show file status bar
set cmdheight=2         " cmd line height
" statusline format
" set statusline=%w%n:%f%m\ %r\ %{fugitive#statusline()}%=Ln:%l/%L,Col:%c%V\ \ %{(&fenc==\"\"?&enc:&fenc).((exists(\"+bomb\")\ &&\ &bomb)?\"\ BOM\":\"\")}\ \ %{&ff}\ \ %Y\ [%P]
set showcmd                     " show input cmd on the right bottom
set autoread                    " auto read file when it was changed by other process
set autowriteall                " auto save file when vim wants to jump to other file
set noswapfile                  " not build the swap file
set backspace=indent,eol,start  " backspace mode
set completeopt-=preview        " not open complete preview window
" set showtabline=2             " always show tab line

" search setting
set hlsearch            " highlight search result
set incsearch           " input search string, jump to result intime
" set ignorecase            " ignore case
" nmap <leader>f :norm yiw<CR>:vimg /\c<C-R>"/j **/*.* \| copen

" folding setting
set foldenable          " Enables folding.
set foldlevel=999       " close auto folding
set foldmethod=indent   " indent folding mode

" encoding
set fileencodings=utf-8,gb2312,gbk,gb18030
set encoding=utf-8

" format
set fileformats=unix,dos

" color
set t_Co=256
hi CursorLine   cterm=NONE ctermbg=240
hi CursorColumn   cterm=NONE ctermbg=240

" complete highlight color
" hi Pmenu ctermbg=Yellow guibg=lightblue

" tab page
" set ctrl + n to create new file or tab
nmap <C-n> :tabnew<cr>

" buffers
" set buffers change keymap
nmap <leader>1 :b1<cr>
nmap <leader>2 :b2<cr>
nmap <leader>3 :b3<cr>
nmap <leader>4 :b4<cr>
nmap <leader>5 :b5<cr>
nmap <leader>6 :b6<cr>
nmap <leader>7 :b7<cr>
nmap <leader>8 :b8<cr>
nmap <leader>9 :b9<cr>
nmap <leader>10 :b10<cr>
nmap <leader>11 :b11<cr>
nmap <leader>12 :b12<cr>
nmap <leader>13 :b13<cr>
nmap <leader>14 :b14<cr>
nmap <leader>15 :b15<cr>
nmap <leader>16 :b16<cr>
nmap <leader>17 :b17<cr>
nmap <leader>18 :b18<cr>
nmap <leader>19 :b19<cr>
nmap <leader>- :bp<cr>
nmap <leader>= :bn<cr>

" plugin
filetype plugin on      " different filetype use different plugin setting
" NERDTree
set rtp+=~/.vim/bundle/nerdtree
" set F10 to show or hide NERDTree
func TreeToggle()
    if !filereadable(expand("%"))
        NERDTreeToggle
        return
    endif
    if g:NERDTree.IsOpen()
        NERDTreeToggle
    else
        NERDTreeFind
    endif
endfunc
map <F10> :call TreeToggle()<cr>

" set nerdtree size
let g:NERDTreeWinSize=25
let g:NERDTreeShowHidden=1      " show hidden files
" start neartree when vim start
" autocmd VimEnter * NERDTree
" point cursor at buffer window
" autocmd VimEnter * wincmd w

" tagbar
set rtp+=~/.vim/bundle/tagbar
" set tagbar width
let g:tagbar_width=70
" set F9 to show or hide tlist
map <F9> :TagbarToggle<CR>
" start Tagbar when this file types open
" autocmd FileType c,cpp,go,js,php,py TagbarOpen
" only add systags while c,cpp open
" autocmd FileType c,cpp set tags+=~/.vim/systags

" echodoc
set rtp+=~/.vim/bundle/echodoc.vim
let g:echodoc#enable_at_startup=1
let g:echodoc#type='popup'

" YouCompleteMe
set rtp+=~/.vim/bundle/YouCompleteMe
let g:ycm_global_ycm_extra_conf='~/.vim/.ycm_extra_conf.py'
" don't show load extra conf message
let g:ycm_confirm_extra_conf=0
" 跳转快捷键
" nnoremap <c-k> :YcmCompleter GoToDeclaration<CR>|
" nnoremap <c-h> :YcmCompleter GoToDefinition<CR>|
nnoremap <c-]> :YcmCompleter GoToDefinitionElseDeclaration<CR>|
" 语法关键字补全
let g:ycm_seed_identifiers_with_syntax=1
" 开启 YCM 基于标签引擎
let g:ycm_collect_identifiers_from_tags_files=1
" 在注释输入中也能补全
let g:ycm_complete_in_comments=1
" 在字符串输入中也能补全
let g:ycm_complete_in_strings=1
" 注释和字符串中的文字也会被收入补全
let g:ycm_collect_identifiers_from_comments_and_strings=1
" 启用语法检查
let g:ycm_show_diagnostics_ui=1
" input two character to trigger complete
let g:ycm_min_num_of_chars_for_completion=2
" don't show preview window when input complete
" let g:ycm_add_preview_to_completeopt=0
" close preview window when leave iw_nsertmode
" let g:ycm_autoclose_preview_window_after_insertion=1

" vim-autopep8
set rtp+=~/.vim/bundle/vim-autopep8
" disable show diff window
let g:autopep8_disable_show_diff=1

" clang-format
set rtp+=~/.vim/bundle/vim-clang-format

" auto-pair
set rtp+=~/.vim/bundle/auto-pairs

" nerd-git-plugin
set rtp+=~/.vim/bundle/nerdtree-git-plugin

" fzf-vim
set rtp+=/opt/fzf
set rtp+=~/.vim/bundle/fzf.vim
" 'Ctrl + p' to find file and open in current tab
map <c-p> :FZF<cr>
" 'Ctrl + f' to find line in current buffer
map <c-f> :BLines<cr>

" gutentags
set rtp+=~/.vim/bundle/vim-gutentags
" let $GTAGSCONF="/usr/local/share/gtags/gtags.conf"
" let $GTAGSLABEL="pygments"
set cscopetag                                   " use cscope for tags command
set cscopeprg='gtags-cscope'                    " replace cscope with gtags-cscope
let g:gutentags_auto_add_gtags_cscope=0         " disable gutentags auto add gtags_cscope, use plus plugin to do this
let g:gutentags_define_advanced_commands = 1    " enable gutentags use advanced commands
let g:gutentags_modules=['gtags_cscope']        " enable gtags module
let g:gutentags_project_root = ['.root']        " define project root dir/file name for gutentags
let g:gutentags_add_default_project_roots = 0   " won't add default roots, only use root dir/file user add
" let g:gutentags_ctags_extra_args = ['--fields=+niazS', '--extra=+q', '--c++-kinds=+px', '--c-kinds=+px']    " ctags extra args
let g:gutentags_cache_dir = expand('~/.cache/tags')         " put tags out of project

" nmap <leader>d yiw:GscopeFind g <C-R>"<cr> :copen<cr>j<cr>

" gutentags_plus
set rtp+=~/.vim/bundle/gutentags_plus

" preview
set rtp+=~/.vim/bundle/vim-preview
" set p to open preview windows
autocmd FileType qf nnoremap <silent><buffer> p :PreviewQuickfix<cr>
" set P to close preview windows
autocmd FileType qf nnoremap <silent><buffer> P :PreviewClose<cr>

" vim-trailing-whitespace
set rtp+=~/.vim/bundle/vim-trailing-whitespace
autocmd BufWritePre * FixWhitespace

" vim-fugitive
set rtp+=~/.vim/bundle/vim-fugitive

" vim-jsbeautify
set rtp+=~/.vim/bundle/vim-jsbeautify
" diy command
command JsFormat call JsBeautify()
command JsonFormat call JsonBeautify()
command JsxFormat call JsxBeautify()
command HtmlFormat call HtmlBeautify()
command CSSFormat call CSSBeautify()

" DoxygenToolkit
set rtp+=~/.vim/bundle/DoxygenToolkit.vim

" vim-airline
set rtp+=~/.vim/bundle/vim-airline
set rtp+=~/.vim/bundle/vim-airline-themes
" set some flags support all situations
let g:airline_left_sep = '|>'
let g:airline_left_alt_sep = '>'
let g:airline_right_sep = '<|'
let g:airline_right_alt_sep = '<'
" set airline theme
let g:airline_theme='badwolf'
" disable count whitespace
let g:airline#extensions#whitespace#enabled = 0
" show tab line
let g:airline#extensions#tabline#enabled = 1
" show buffers num
let g:airline#extensions#tabline#buffer_nr_show = 1

" ack.vim
set rtp+=~/.vim/bundle/ack.vim
" highlight search word
let g:ackhighlight = 1
" search current file
nmap <leader>f yiw:Ack!<space>-i<space><C-R>"<space>%
" search all file
nmap <leader>F yiw:Ack!<space>-i<space><C-R>"<space>**/*.*
" search current file
vmap <leader>f y:Ack!<space>-i<space><C-R>"<space>%
" search all file
vmap <leader>F y:Ack!<space>-i<space><C-R>"<space>**/*.*

" vim-go
set rtp+=~/.vim/bundle/vim-go
" autocmd FileType go nmap <c-]> :GoDef<cr>

" vim-ale
set rtp+=~/.vim/bundle/ale
let g:ale_pattern_options = {
\   '\.min\.js$': {
\       'ale_enabled': 0,
\   },
\   '\.c[p]*$': {
\       'ale_enabled': 0,
\   },
\   '\.h[p]*$': {
\       'ale_enabled': 0,
\   },
\}

" ctrlsf.vim
set rtp+=~/.vim/bundle/ctrlsf.vim
" search current file
" nmap <leader>f yiw:CtrlSF<space><C-R>"<space>%
" search all file
" nmap <leader>F yiw:CtrlSF<space><C-R>"<space>**/*.*
" search current file
" vmap <leader>f y:CtrlSF<space><C-R>"<space>%
" search all file
" vmap <leader>F y:CtrlSF<space><C-R>"<space>**/*.*

" nerdcommenter
set rtp+=~/.vim/bundle/nerdcommenter
let g:NERDSpaceDelims = 1
nmap <leader><space> <leader>c<space>
vmap <leader><space> <leader>c<space>

" indentLine
" set rtp+=~/.vim/bundle/indentLine
" let g:indentLine_color_term = 28
" let g:indentLine_conceallevel = 2
" let g:indentLine_setConceal = 0
