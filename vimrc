"--------------------------------------
" basic settings
"-------------------------------------- 
set nu					" show line number
syntax on				" code highlight
colorscheme molokai		" highlight theme molokai
set tabstop=4           " think 4 space to 1 tab, think '\t' to 4 space
set softtabstop=4       " tab input to 4 space width
set shiftwidth=4        " change line width is 4 space
set expandtab           " input tab, true 4 space
set autoindent          " auto indent
set smartindent			" smart indent
set ruler				" show cursor pos status
set cursorline			" highlight cursor line
filetype indent on		" different filetype use differnt indent
set laststatus=2        " show file status bar
" statusline format
set statusline=%w%n:%f%m\ %r%=Ln:%l/%L,Col:%c%V\ \ %{(&fenc==\"\"?&enc:&fenc).((exists(\"+bomb\")\ &&\ &bomb)?\"+\":\"\")}\ \ %{&ff}\ \ %Y\ [%P]
set showcmd             " show input cmd on the right bottom
set autoread            " auto read file when it was changed by other process
set autowriteall        " auto save file when vim wants to jump to other file
set noswapfile          " do not build swap file

" search setting
set hlsearch			" highlight search result
set incsearch			" input search string, jump to result intime
set ignorecase			" ignore case
" use <leader>f to search ignore case cursor word in all files and open quickfix
nmap <leader>f :norm yiw<CR>:vimg /\c<C-R>"/j **/*.* \| copen

" folding setting
set foldenable			" Enables folding.
set foldlevel=999		" close auto folding
set foldmethod=indent	" indent folding mode

" encoding
set fileencodings=utf-8,gb2312,gbk,gb18030

" format
set fileformats=unix,dos

"----------------------------------------
" plugin settings
"----------------------------------------
filetype plugin on      " different filetype use different plugin setting
" NERDTree
set rtp+=~/.vim/bundle/nerdtree
" set F10 to show or hide NERDTree
map <F10> :NERDTreeToggle<CR>
let g:NERDTreeShowHidden=1      " show hidden files

" taglist
set rtp+=~/.vim/bundle/taglist.vim
" set F9 to show or hide tlist
map <F9> :TlistToggle<CR>       
let Tlist_Exit_OnlyWindow=1     " if tlist is the last window, then exit vim
let Tlist_Use_Right_Window=1    " show tlist window on the right
let Tlist_Show_One_File=1       " show one file taglist
set tags+=~/.vim/systags

" echofunc
" set rtp+=~/.vim/bundle/echofunc

" tabnine
set rtp+=~/.vim/bundle/tabnine-vim

" clang-format
set rtp+=~/.vim/bundle/vim-clang-format

" auto-pair
set rtp+=~/.vim/bundle/auto-pairs

" nerd-git-plugin
set rtp+=~/.vim/bundle/nerdtree-git-plugin

" fzf-vim
set rtp+=/opt/fzf
set rtp+=~/.vim/bundle/fzf.vim
" 'Ctrl + p' to find file
map <c-p> :FZF<cr>
" 'Ctrl + f' to find line
map <c-f> :Lines<cr>

" gtags
set rtp+=~/.vim/bundle/gtags
set cscopetag                   " use cscope for tags command
set cscopeprg='gtags-cscope'    " replace cscope with gtags-cscope

" gutentags
set rtp+=~/.vim/bundle/vim-gutentags
let g:gutentags_auto_add_gtags_cscope=0                                         " disable gutentags auto add gtags_cscope, use plus plugin to do this
let g:gutentags_define_advanced_commands = 1                                    " enable gutentags use advanced commands
let g:gutentags_modules=['ctags', 'gtags_cscope']                               " enable gtags module
let g:gutentags_project_root = ['.root', '.svn', '.git', '.hg', '.project']     " define project root dir/file name for gutentags
let g:gutentags_ctags_extra_args = ['--fields=+niazS', '--extra=+q', '--c++-kinds=+px', '--c-kinds=+px']    " ctags extra args
let g:gutentags_cache_dir = expand('~/.cache/tags')                             " put tags out of project

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
autocmd BufWritePre * FixWhitespace         " trailing space on save
