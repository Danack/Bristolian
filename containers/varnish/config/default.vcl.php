<?php


$varnishPassAllString = <<< CACHING

# return(pass) is not used - aka caching is enabled
CACHING;


if (${'varnish.pass_all_requests'} === true) {
    $varnishPassAllString .= <<< TEXT
# pass all requests - aka caching is disabled
return(pass);
TEXT;

}


$config = <<< END

vcl 4.0;

import std;
# import geoip2;


# Default backend definition. Set this to point to your content server.
# These do use the docker network stack.
backend default {
    .host = "caddy";
    .port = "80";
}

sub vcl_init {
    # new country = geoip2.geoip2("/var/app/containers/varnish/GeoLite2-Country_20181225/GeoLite2-Country.mmdb");
}

# acl purge {
#  "localhost";
#  "192.168.55.0"/24;
# }

############################################################
#
#
############################################################
sub add_country_code_if_required {

  if (
        req.url ~ "/font/"             ||
        req.url ~ "/images/"           ||
        req.url ~ "/js/"               ||
        req.url ~ "/css/"
        # || req.url !~ "\.(gif|jpg|jpeg|swf|flv|mp3|mp4|pdf|ico|png|css|js)(\?.*|)$"
  ) {
    # don't bother doing country code lookup for static files
  }
  else {
    set req.http.X-IP-to-use = client.ip;

    # Allow location to be spoofed via header for testing
    if (req.http.X-IP-Spoof ~ "[0-9]") {
      set req.http.X-IP-to-use = req.http.X-IP-Spoof;
    }

  # commented out as libgeoip isn't loaded yet.
  # needs to be recompiled.
  #  set req.http.X-country-code = country.lookup(
  #    "country/iso_code",
  #    std.ip(req.http.X-IP-to-use,"0.0.0.0")
  #  );
  }
}


############################################################
# vcl_recv is called at the beginning of a request, after the complete request has been received
# and parsed. Its purpose is to decide whether or not to serve the request, how to do it, and, if
# applicable, which backend to use.
# In vcl_recv you can also alter the request. Typically you can alter the cookies and add and remove request headers.
#
# Note that in vcl_recv only the request object, req is available.
#############################################################

sub vcl_recv {

  if (req.method != "GET" && # Disallow custom methods
    req.method != "HEAD" &&
    req.method != "POST" &&
    req.method != "OPTIONS" &&
    req.method != "PUT" &&
    req.method != "DELETE") {
      return (synth(405, "Method Not Allowed"));
  }

  # Health check
  if (req.url ~ "^/(health|status)$") {
     return (synth(200, "Ok"));
  }

  
  set req.http.X-IP-original = client.ip;

  set req.backend_hint = default;

  # Command to clear complete cache for all URLs and all sub-domains
  # curl -X XCGFULLBAN http://example.com
  if (req.method == "XCGFULLBAN") {
    ban("req.http.host ~ .*");
    return (synth(200, "Full cache cleared"));
  }

  call add_country_code_if_required;

  # if (req.method == "PURGE") {
  #   ##PROD_PURGE_ALLOW_CHECK##
  #   return (purge);
  # }

  if (req.method != "GET" && req.method != "HEAD") {
      return(pass);
  }

  // Remove has_js and Google Analytics __* cookies.
  set req.http.Cookie = regsuball(req.http.Cookie, "(^|;\s*)(_[_a-z]+|has_js)=[^;]*", "");

  // Remove a ";" prefix, if present.
  set req.http.Cookie = regsub(req.http.Cookie, "^;\s*", "");

  if (req.restarts == 0) {
    if (req.http.x-forwarded-for) {
      set req.http.X-Forwarded-For =
      req.http.X-Forwarded-For + ", " + client.ip;
    } else {
      set req.http.X-Forwarded-For = client.ip;
    }
  }

  if (req.url ~ "^/ping.php") {
    return (pipe);
  }

  ${'varnishPassAllString'}  

  return(hash);
}


#############################################################
# The data on which the hashing will take place
#############################################################
sub vcl_hash {
 hash_data(req.url);
 if (req.http.host) {
       hash_data(req.http.host);
 } else {
       hash_data(server.ip);
 }

 # If the client supports compression, keep that in a different cache
 if (req.http.Accept-Encoding) {
    hash_data(req.http.Accept-Encoding);
 }

  # We add the cookie in the mix with the hash because we actually set cr_layout
  # It should be OK in this particular instance if the number of cookies will not grow
  hash_data(req.http.Cookie);

  # If a country-code was looked up, use that also.
  if (req.http.X-country-code) {
    hash_data(req.http.X-country-code);
  }
}


###############################################################################
# vcl_fetch is called after a document has been successfully retrieved from the
# backend.
# Normal tasks her are to alter the response headers, trigger ESI processing, try
# alternate backend servers in case the request failed.
# In vcl_fetch you still have the request object, req, available. There is also a
# backend response, beresp. beresp will contain the HTTP headers from the backend.
###############################################################################
# sub vcl_fetch {
#    set beresp.ttl = 5m;
# }

###############################################################################
# Called before sending the backend request. In this subroutine you typically alter the request before it gets to the backend.
###############################################################################
sub vcl_backend_fetch {

}

###############################################################################
# Called after the response headers have been successfully retrieved from the backend.
###############################################################################
sub vcl_backend_response {

  # Happens after we have read the response headers from the backend.
  #
  # Here you clean the response headers, removing silly Set-Cookie headers
  # and other mistakes your backend does.
  #if (
  #  beresp.status == 500 ||
  #  beresp.status == 502 ||
  #  beresp.status == 503 ||
  #  beresp.status == 504) {
  #  return (abandon);
  #}

  # Do ESI processing
  set beresp.do_esi = true;

  if (!beresp.http.cache-control) {
    set beresp.ttl = 60 s;
    set beresp.http.cache-control = "public, max-age=60";
  }
}

###############################################################################
# Called before any object except a vcl_synth result is delivered to the client.
# Happens when we have all the pieces we need, and are about to send the
# response to the client.
#
# You can do accounting or modifying the final object here.
###############################################################################
sub vcl_deliver {

  # Insert Diagnostic header to show Hit or Miss
  if (obj.hits > 0) {
      set resp.http.X-Cache = "HIT";
      set resp.http.X-Cache-Hits = obj.hits;
  }
  else {
      set resp.http.X-Cache = "MISS";
  }
}


###############################################################################
# Called to deliver a synthetic object. A synthetic object is generated in
# VCL, not fetched from the backend. Its body may be constructed using the
# synthetic() function.
###############################################################################
sub vcl_synth {
    if (resp.status == 850) {
       set resp.http.Location = req.http.x-https-redirect;
       set resp.status = 302;
       return (deliver);
    }
}


END;

return $config;