        <div class="col-xs-12 col-sm-9">
          <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
          </p>

        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>History</h1>
          <p>Nagrestconf started life around 2010 in Nokia's Advanced
          Operations. It was a critical part of a larger system named Shared
          Nagios that had a number of goals: To provide a single viewing
          console in their Network Operations Centre (NOC) to show the status
          of all servers in all data centres in all countries; To remove the
          bottleneck of one department handling nagios configuration by
          decentralising the configuration, delegating to other nagios
          administrators, but maintaining a shared nagios cluster for all to
          use; And finally to allow automation so that load balancers,
          controlling a large collection of blade servers, could enable or
          disable monitoring for servers as they were brought up, or taken
          down, depending on load. At this point Nagrestconf was just the REST
          API, which, after much discussion took about two weeks to write.</p>

          <p>Choices were made at this stage on preferred programming languages
          and architecture. For the REST interface, PHP and Shell scripts were
          chosen as it was more likely that existing administrators could fix
          code written in these languages. Additionally, for the programming
          features that were actually required, shell scripts were the easiest
          option. The csv file format was also chosen so that administrators
          could edit those files directly if something broke, and it was a
          requirement that any task could be completed from the command
          line.</p>

          <p>Development on Nagrestconf continued as the author moved to other
          companies that required these features and their business
          requirements directed further development efforts. With source size
          currently standing at about 30,000 lines of code, it's not surprising
          that Nagrestconf has become a good base to build upon to create
          customised monitoring solutions.</p>

          <p>A few large companies have contributed developer time to
          Nagrestconf including: Nokia, the BBC Archive, United Health, and
          Irdeto, so their own unique requirements are now built into it, but
          the main driving force behind all development is - to keep things
          simple!</p>

        </div>
        <!-- /Content -->

        </div><!--/span-->
