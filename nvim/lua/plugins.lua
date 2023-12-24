vim.cmd [[packadd packer.nvim]]

return require('packer').startup(function(use)
    use 'wbthomason/packer.nvim'
    use {"akinsho/toggleterm.nvim", tag = '*', config = function()
        require("toggleterm").setup()
    end}
    use 'scrooloose/nerdcommenter'
    use 'nvim-tree/nvim-tree.lua'
    use 'nvim-tree/nvim-web-devicons'
    use 'Xuyuanp/nerdtree-git-plugin'
    use {
        'nvim-telescope/telescope.nvim',
        requires = { {'nvim-lua/plenary.nvim'} }
    }
    use 'dyng/ctrlsf.vim'
    use 'lewis6991/gitsigns.nvim'
    use 'tpope/vim-fugitive'
    use 'vim-airline/vim-airline'
    use 'vim-airline/vim-airline-themes'
    use 'bronson/vim-trailing-whitespace'
    use 'jiangmiao/auto-pairs'
    use 'preservim/tagbar'
    use 'sainnhe/edge'
    use 'mileszs/ack.vim'
    use 'ray-x/go.nvim'
    use 'williamboman/mason.nvim'
    use 'neovim/nvim-lspconfig'
    use {
        'ojroques/nvim-lspfuzzy',
        requires = { { 'junegunn/fzf' }, {'junegunn/fzf.vim'} },
    }
    use {
        'hrsh7th/nvim-cmp',
        requires = {
            { 'hrsh7th/cmp-nvim-lsp' },
            { 'hrsh7th/cmp-buffer' },
            { 'hrsh7th/cmp-path' },
            { 'hrsh7th/cmp-cmdline' },
            { 'hrsh7th/cmp-vsnip' },
            { 'hrsh7th/vim-vsnip' },
        },
    }
    use 'rafamadriz/friendly-snippets'
    use 'onsails/lspkind-nvim'
end)
