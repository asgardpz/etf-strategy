<?php
namespace App\Services;
use Carbon\Carbon; use Illuminate\Support\Facades\Http; use RuntimeException;
class TwseService
{
    private array $config; public function __construct(){ $this->config=config('etf.twse'); }
    private function client(){ return Http::timeout($this->config['timeout'])->withHeaders(['User-Agent'=>$this->config['user_agent'],'Accept'=>'application/json,text/html;q=0.9,*/*;q=0.8']); }
    public function dividendRows(int $year): array
    {
        $url=$this->config['base_url'].'/zh/ETFortune/dividendList';
        $response=$this->client()->get($url,['startDate'=>(string)$year,'endDate'=>(string)$year]);
        if(!$response->successful()) throw new RuntimeException('TWSE ETF dividend page HTTP '.$response->status());
        $html=$response->body(); $dom=new \DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8">'.$html); $xpath=new \DOMXPath($dom); $rows=[];
        foreach($xpath->query('//table//tr') as $tr){ $cells=[]; foreach($xpath->query('./th|./td',$tr) as $cell){$cells[]=trim(preg_replace('/\\s+/u',' ',html_entity_decode($cell->textContent,ENT_QUOTES,'UTF-8')));} if(count($cells)<6)continue; if(!preg_match('/^\\d{4,6}[A-Z]?$/',$cells[0]))continue;
            $ex=$this->parseTwseDate($cells[2]); $record=$this->parseTwseDate($cells[3]); $pay=$this->parseTwseDate($cells[4]); $div=$this->parseNumber($cells[5]); if(!$ex)continue;
            $rows[]=['code'=>$cells[0],'name'=>$cells[1],'ex_dividend_date'=>$ex,'record_date'=>$record,'pay_date'=>$pay,'dividend_amount'=>$div,'announcement_year'=>(string)$year,'source_url'=>$response->effectiveUri() ?: $url,'raw_data'=>$cells];
        }
        if(!$rows) throw new RuntimeException('TWSE ETF dividend page returned no parseable rows.'); return $rows;
    }
    public function quote(string $code): array
    {
        $response=$this->client()->get($this->config['quote_url'],['ex_ch'=>'tse_'.$code.'.tw']);
        if(!$response->successful()) throw new RuntimeException('TWSE quote HTTP '.$response->status()); $json=$response->json(); $item=$json['msgArray'][0]??null; if(!$item) throw new RuntimeException('TWSE quote not available for '.$code);
        $price=$this->parseNumber($item['z']??null); if($price===null)$price=$this->parseNumber($item['y']??null);
        return ['price'=>$price,'reference_price'=>$this->parseNumber($item['y']??null),'change_amount'=>$this->parseNumber($item['d']??null),'change_percent'=>$this->parseNumber($item['p']??null),'quote_time'=>$this->parseQuoteTime($item),'raw_data'=>$item];
    }
    public function holidays(int $year): array
    {
        $url=$this->config['openapi_url'].'/holidaySchedule/holidaySchedule'; $response=$this->client()->get($url,['response'=>'json','queryYear'=>$year]);
        if(!$response->successful()) return []; $json=$response->json(); $out=[]; foreach(($json??[]) as $row){$date=$row['Date']??$row['日期']??null;$name=$row['Name']??$row['名稱']??'';if($date)$out[$date]=$name;} return $out;
    }
    private function parseTwseDate(?string $value): ?string { if(!$value)return null; if(preg_match('/(\\d{4})[\\/-](\\d{1,2})[\\/-](\\d{1,2})/u',$value,$m))return Carbon::create((int)$m[1],(int)$m[2],(int)$m[3])->toDateString(); if(preg_match('/(\\d{3})年(\\d{1,2})月(\\d{1,2})日/u',$value,$m))return Carbon::create((int)$m[1]+1911,(int)$m[2],(int)$m[3])->toDateString(); return null; }
    private function parseNumber($value): ?float {if($value===null)return null;$s=str_replace([',','%','--','-'],'',$value);return is_numeric(trim($s))?(float)$s:null;}
    private function parseQuoteTime(array $item): string { $date=$item['d']??date('Ymd'); $t=$item['t']??date('H:i:s'); if(preg_match('/^\\d{4}\\d{2}\\d{2}$/',$date))return substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2).' '.($t?:'00:00:00'); return now()->toDateTimeString(); }
}
