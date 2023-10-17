lua require('plugins')

set nocp                " vi not compatible mode
set nu                  " show line number
set tabstop=4           " think 4 space to 1 tab, think '\t' to 4 space
set softtabstop=4       " tab input to 4 space width
set shiftwidth=4        " change line width is 4 space
set expandtab           " input tab, true 4 space
set ruler               " show cursor pos status
set cursorline          " highlight cursor line
set cursorcolumn        " highlight cursor column
set noswapfile                  " not build the swap file
set backspace=indent,eol,start  " backspace mode
set cmdheight=2         " cmd line height
set showcmd             " show input cmd on the right bottom
set autoread            " auto read file when it was changed by other process
set autowriteall        " auto save file when vim wants to jump to other file
set noswapfile          " not build the swap file

" set mouse=              " disable mouse
vnoremap <C-y> "+y

" search setting
set hlsearch            " highlight search result
set incsearch           " input search string, jump to result intime

" folding setting
set foldenable          " Enables folding.
set foldlevel=999       " close auto folding
set foldmethod=indent   " indent folding mode

" encoding
set fileencodings=utf-8,gb2312,gbk,gb18030
set encoding=utf-8

" color
set t_Co=256

" tagbar
" set tagbar width
let g:tagbar_width=70
" set F9 to show or hide tlist
map <F9> :TagbarToggle<CR>

" nvim-tree
lua require("nvim-tree").setup({
    \ sort_by = "case_sensitive",
    \ git = {
        \ ignore = false
    \ }
\ })
" set F10 to show or hide NERDTree
func TreeToggle()
    if !filereadable(expand("%"))
        NvimTreeToggle
        return
    endif
    NvimTreeFindFileToggle
endfunc
map <F10> :call TreeToggle()<cr>

" nvim-lspconfig
lua require('lspconfig').gopls.setup({})
lua require('lspconfig').golangci_lint_ls.setup({})
lua require('lspconfig').tsserver.setup({})
lua require('lspconfig').clangd.setup({})
lua require('lspconfig').cmake.setup({})
lua require('lspconfig').docker_compose_language_service.setup({})
lua require('lspconfig').eslint.setup({})
lua require('lspconfig').html.setup({})
lua require('lspconfig').jsonls.setup({})
lua require('lspconfig').lua_ls.setup({})
lua require('lspconfig').pyright.setup({})
lua require('lspconfig').vimls.setup({})
lua require('lspconfig').bashls.setup({})
" goto declaration
nmap <silent> gD <cmd>lua vim.lsp.buf.declaration()<cr>
" goto definition
nmap <silent> gd <cmd>lua vim.lsp.buf.definition()<cr>
" goto references
nmap <silent> gr <cmd>lua vim.lsp.buf.references()<cr>
" goto implementation
nmap <silent> gi <cmd>lua vim.lsp.buf.implementation()<cr>
" get code action
nmap <silent> gc <cmd>lua vim.lsp.buf.code_action()<cr>
nmap <silent> K <cmd>lua vim.lsp.buf.hover()<cr>
" go to diagnostic
nmap <silent> <leader>dp <cmd>lua vim.diagnostic.goto_prev()<cr>
nmap <silent> <leader>dn <cmd>lua vim.diagnostic.goto_next()<cr>
" format
nmap <M-F> <cmd>lua vim.lsp.buf.format { async=true }<cr>
vmap <M-F> <cmd>lua vim.lsp.buf.format { async=true }<cr>

" nvim-cmp
lua require('nvim-cmp')

" nvim-lspfuzzy
lua require('lspfuzzy').setup {}

lua require('mason').setup({
    \ automatic_installation = true,
    \ github = { download_url_template = "https://ghproxy.com/https://github.com/%s/releases/download/%s/%s" },
\ })

" Toggleterm
" use ctrl + t to open a terminal at bottom
map <c-t> :ToggleTerm<cr>

" Telescope
" 'Ctrl + p' to find file and open in current tab
if filereadable(fnamemodify('.git/config', ':p'))
    nmap <c-p> :Telescope git_files<cr>
else
    nmap <c-p> :Telescope find_files<cr>
endif
" 'Ctrl + f' to find line in current buffer
map <c-f> :Telescope current_buffer_fuzzy_find<cr>
" 'leader + b' to find buffers
map <leader>b :Telescope buffers<cr>
" 'leader + f' to find line in all file
map <leader>f :Telescope live_grep<cr>
" 'leader + t' to find tags in current buffer
map <leader>t :Telescope tags<cr>

" ack
" 'leader + Shift + F' to find text under cursor in all file use ack
nmap <leader>F yiw:Ack!<space>-i<space><C-R>"
" search selected text
vmap <leader>F y:Ack!<space>-i<space><C-R>"

" airline
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

" nerdcommenter
let g:NERDSpaceDelims = 1
let g:NERDCreateDefaultMappings = 0
nmap <leader><space> <Plug>NERDCommenterToggle
vmap <leader><space> <Plug>NERDCommenterToggle

" vim-trailing-whitespace
autocmd BufWritePre * FixWhitespace

" gitsigns
lua require('gitsigns').setup { current_line_blame = true, current_line_blame_formatter = '<author> [<author_time:%Y-%m-%d>] * <summary>' }

" edge
if exists('g:vscode')
    " vscode
else
    let g:edge_style = 'aura'
    let g:edge_better_performance = 1
    colorscheme edge
    hi CursorLine   cterm=NONE ctermbg=238
    hi CursorColumn   cterm=NONE ctermbg=238
    hi Normal cterm=NONE ctermbg=NONE
    hi Visual ctermbg=240 guibg=240
endif
