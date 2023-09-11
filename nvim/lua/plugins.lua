vim.cmd [[packadd packer.nvim]]

return require('packer').startup(function(use)
    use 'wbthomason/packer.nvim'
    use {"akinsho/toggleterm.nvim", tag = '*', config = function()
        require("toggleterm").setup()
    end}
    use 'neoclide/coc.nvim'
    use 'scrooloose/nerdcommenter'
    use 'nvim-tree/nvim-tree.lua'
    use 'nvim-tree/nvim-web-devicons'
    use 'Xuyuanp/nerdtree-git-plugin'
    use {
        'nvim-telescope/telescope.nvim',
        requires = { {'nvim-lua/plenary.nvim'} }
    }
    use 'lewis6991/gitsigns.nvim'
    use 'tpope/vim-fugitive'
    use 'vim-airline/vim-airline'
    use 'vim-airline/vim-airline-themes'
    use 'bronson/vim-trailing-whitespace'
    use 'jiangmiao/auto-pairs'
    use 'preservim/tagbar'
    use 'sainnhe/edge'
    use 'mileszs/ack.vim'
    use 'fatih/vim-go'
    use 'ludovicchabant/vim-gutentags'
end)
