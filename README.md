# CommodityTracker
>A commodity market tracker using Space Engineers econ data.


This will use the new torch plugin to take econ data and use it to show market data.
Initially we want to be able to ingest current market data and show current values.


![](images/repository-card.png)


## Usage example

This will allow all your users to see how your system's various commodity markets are doing and check prices.

As a store manager maybe you want to use this to undercut your competitors!


_For more examples and usage, please refer to the [Wiki][wiki]._

## Development setup
This will require the torch econ plugin.  But it's still in early alpha

## Release History

* 0.0.1
    * Initial commit.  Taking Hobobot's code and putting it here.
* 0.0.2
    * Added a sqlite test db, and various initial files for github and the system.
* 0.0.3
    * put a basic sanitizer in for the various post data.
* 0.0.4
    * Started building the update page to accept the post data and deal with it.
* 0.0.5
    * Continued to flesh out the update page and the functions it needs.
* 0.0.6
    * Lots of DB changes. Added a common php file and made the SQL connection use PDO so we can use sqlite/mysql whatever as we work. fleshed out more of the update code.  Also started workign on dispaying data from the DB to the users.
*.0.0.7
    * New logo and some cleanup in images
*.0.0.8 Brgan implementing a Drupal custom entities storage solution for the data we are gathering.

## Meta

NebulonCluster – [discord] – adminsl@nebuloncluster.net

Distributed under the MIT license. See ``LICENSE`` for more information.

[https://github.com/mrdatawolf/github-link](https://github.com/mrdatawolf/)

## Contributing

1. Fork it (<https://github.com/mrdatawolf/CommodityTracker/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

<!-- Markdown link & img dfn's -->
[wiki]: https://github.com/mrdatawolf/CommodityTracker/wiki
[discord]: https://discord.gg/8QEQBq
