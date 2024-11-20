local cmp = require("cmp")
local lspkind = require("lspkind")
local nvim_lsp = require('lspconfig')

-- nvim-lspconfig
nvim_lsp.gopls.setup({})
nvim_lsp.golangci_lint_ls.setup({})
nvim_lsp.ts_ls.setup({})
nvim_lsp.clangd.setup({})
nvim_lsp.cmake.setup({})
nvim_lsp.docker_compose_language_service.setup({})
nvim_lsp.eslint.setup({})
nvim_lsp.html.setup({})
nvim_lsp.jsonls.setup({})
nvim_lsp.lua_ls.setup({})
nvim_lsp.pyright.setup({})
nvim_lsp.vimls.setup({})
nvim_lsp.bashls.setup({})

cmp.setup({
    snippet = {
        expand = function(args)
            require('luasnip').lsp_expand(args.body) -- 使用 LuaSnip 作为代码片段引擎
        end,
    },
    -- 来源
    sources = cmp.config.sources({
        { name = 'nvim_lsp' },
        { name = 'luasnip' },
        -- For vsnip users.
        { name = 'vsnip' },
    }, { { name = 'buffer' },
        { name = 'path' }
    }),
    mapping = {
        ["<c-p>"] = cmp.mapping.select_prev_item(),
        ["<c-n>"] = cmp.mapping.select_next_item(),
        ["<CR>"] = cmp.mapping.confirm({
            select = true,
            behavior = cmp.ConfirmBehavior.Replace,
        }),
        ["<TAB>"] = cmp.mapping.confirm({
            select = true,
            behavior = cmp.ConfirmBehavior.Replace,
        }),
    },
    formatting = {
        format = lspkind.cmp_format({
            with_text = true, -- do not show text alongside icons
            maxwidth = 50,    -- prevent the popup from showing more than provided characters (e.g 50 will not show more than 50 characters)
            before = function(entry, vim_item)
                -- Source 显示提示来源
                vim_item.menu = "[" .. string.upper(entry.source.name) .. "]"
                return vim_item
            end
        })
    },
})

-- 配置 lsp_signature 插件
require 'lsp_signature'.setup({
    bind = true,      -- This is mandatory, otherwise border config won't get registered.
    handler_opts = {
        border = "single" -- double, single, shadow, none
    }
})
