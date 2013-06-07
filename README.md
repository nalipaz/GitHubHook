## GitHub Post-Receive Deployment Hook

Deploying applications to development, staging and production never been so easy with GitHub Post-Receive Deployment Hook script!  
This fork sponsored by the [Los Angeles Web Design Firm](http://stitch-technologies.com/) *Stitch Technologies*.

### Installation

Clone the script:

<pre><code>$ <strong>git clone https://github.com/kwangchin/GitHubHook.git</strong>
</code></pre>

Go to your `GitHub repo` &gt; `Admin` &gt; `Service Hooks`, select `Post-Receive URLS` and enter your hook URL like this:

// CREATE NEW SCREENSHOT

### How It Works

GitHub provides [Post-Receive Hooks](http://help.github.com/post-receive-hooks/) to allow HTTP callback with a HTTP Post. We then create a script for the callback to deploy the systems automatically.

Describe how branches/tags work.

You then can have a brief look into `example.class.GithubHook.php`, an example of how you can extend the GithubHook PHP class for your needs.  Create a renamed copy of this file called GithubHookCustom.php by duplicating it and removing the `example.` from the file name.

Next you need to minimally add your branch information into it.

<pre><code>&lt;?php
// UPDATE ME
</code></pre>

In this example, we enabled the debug log for messages with timestamp. You can disable this by commenting or removing the line `$hook->enableDebug()`

We have a staging site and a production site in this example. You can add more branches easily with `$hook->addBranch()` method if you have more systems to deploy.

## 

### Security

Worry about securities? We have enabled IP checking by default to allow only GitHub hook addressesthrough, example.GithubHookCustom.php has a list of all IP's we have encountered from Github's web hook calls and we update it when we notice more.
