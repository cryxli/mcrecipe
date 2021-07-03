# mcrecipe

This project implements a syntax plugin for [Dokuwiki](https://www.dokuwiki.org/plugins).
It adds a `<recipe>` tag to the syntax that lets you define any kind of (crafting) recipes.

![Example](https://raw.githubusercontent.com/wiki/cryxli/mcrecipe/images/recipe_ex_01.png)

The goal is to only use resources that are already present within the wikispace.

## Concept

Every mod (that is [Minecraft Forge](http://www.minecraftforge.net/)'s way of talking about extensions) has its own sub-space in the wiki.

Every mod's sub-space is located within the root space `:mods:`. (May become configurable in the future.)

Every image of a block or item is named exactly like its description page in the wiki. And is provided as PNG.

*Example: The vanilla block Smooth Stone would have its page on `:mods:minecraft:stone`.*

*Example: The vanilla block Smooth Stone would have an image under `:mods:minecraft:stone.png`.*

## Usage

Anywhere on a wiki page you can add the recipe tag.

*Example on how to describe cooking Cobblestone into Smooth Stone:*

```
<recipe>
size 1x1
input minecraft:cobblestone
output minecraft:stone
tool minecraft:furnace
</recipe>
```

### Size

The first "command" of a recipe is `size`. It describes how many spaces the crafting grid should have.

It understands any combination of "width times height" including odd shaped recipes like 3x1 or 1x2.

### Input

For each row on the crafting grid as defined by `size` there should be one line of `input`.

An input line has as many "arguments", items that is, as the width of the crafting grid. 

If you want to leave a space = having no item in a place, just write `air`.

*Example of torches recipe an a 3x3 grid:*

```
<recipe>
size 3x3
input air
input air minecraft:coal
input air minecraft:stick
output minecraft:torch,4
</recipe>
```

### Output

Define the resulting items of the recipe. Output only takes a single item as argument.

### Tool

To indicate the "machine" used with the recipe you can change it to any block.

If this line is omitted, the crafting table is assumed and displayed.

### Item stacks

Both commands `input` and `output` take item stacks as arguments in the form:

```
"wiki page" "," "amount"

minecraft:torch,4
minecraft:cobblestone,1
```

The spaces and double-quotes are only there to illustrate the pieces from which an item stack is composed.

"wiki page" is prefixed with `:mods:` and therefore resolves the detail page and when suffixed again with `.png` the image of a block.

"amount" defines the quantity of an item that is used in the recipe. If omitted exacly one is assumed.
Only amounts larger than one are indicated with numbers.

Note that `tool` also takes an item stack as an argument, but it will ignore any amounts.

### Thanks

Thanks to [drcrazy](https://github.com/drcrazy) for the Minecraft like styling! And thanks to [dali99](https://github.com/dali99) for updating to [Hogfather](https://www.dokuwiki.org/changes#release_2020-07-29_hogfather).

