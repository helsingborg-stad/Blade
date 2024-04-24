<style>
  .error-table {
      font-family: sans-serif;
      width: calc(100%% - 32px);
      border-collapse: collapse;
      margin: 16px;
      background: #fff;
      box-shadow: 0 0 16px rgb(0,0,0,.25);
      border-radius: 4px;
      overflow: hidden;
      outline: 2px solid #7f1d1d;
      outline-offset: -2px;
      box-sizing: border-box;
  }

  .error-table pre {
      font-family: inherit;
      white-space: pre-wrap;
      margin: 0;
  }
  
  .error-table thead th {
      background: #7f1d1d;
      color: #fff;
      padding: 16px;
      font-size: 1.5em;
  }

  .error-table, 
  .error-table tr,
  .error-table td {
      border: 2px solid #7f1d1d;
      padding: 8px 16px;
  }
  .error-table td.stacktrace {
      padding: 16px;
  }

  .error-table span.error-line {
      background: #7f1d1d;
      color: #fff;
      white-space:normal;
  }
</style>

<table class="error-table">
  <thead>
      <tr>
          <th colspan="2">
            <h2>A view rendering issue has occurred</h2>
          </th>
      </tr>
  <tr>
      <td><strong>Error Message:</strong></td>
      <td>{{ $message }}</td>
  </tr>
  <tr>
      <td><strong>Error Line:</strong></td>
      <td>{{ $line }}</td>
  </tr>
  <tr>
      <td><strong>Error Source File:</strong></td>
      <td>{{ $source }}</td>
  </tr>
  <tr>
      <td><strong>Source code (line {{ $line }}):</strong></td>
      <td>
          <code>{{ $code['before'] }}
            <br/>
            <span class="error-line">
              {{ $code['current'] }}
            </span>
            <br/>
            {{ $code['after'] }}
          </code>
      </td>
  </tr>
  <tr>
  @if($viewPaths)
  <tr>
      <td><strong>View paths:</strong></td>
      <td>{{ $viewPaths }}</td>
  </tr>
  @endif
  @if($cachePath)
  <tr>
      <td><strong>Cache path:</strong></td>
      <td>{{ $cachePath }}</td>
  </tr>
  @endif
  <td><strong>Stacktrace:</strong></td>
      <td class="stacktrace">
          <pre>{{ $stacktrace }}</pre>
      </td>
  </tr>
</table>